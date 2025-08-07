// Global variables
var container, stats, controls;
var camera, scene, renderer, light;
var object;
var width = window.innerWidth;
var height = window.innerHeight;
var pixelRatio = window.devicePixelRatio;
var currentTexture = null;
var selectedMaterial = null;
var svgPath = 'assets/test1.svg';
var colorState = {
    'Torso': '#ffffff',
    'Sleeve-1': '#ffffff',
    'Sleeve-2': '#ffffff'
}
var fabricCanvas;
let cachedSvgDoc = null;

// Adjust height if needed
if (width < height) {
    height = width;
}

// Initialize everything once the window loads
window.addEventListener('load', function() {
    if (!Detector.webgl) {
        Detector.addGetWebGLMessage();
        return;
    }

    initSvgCache();
    init();
    setupLights();
    initFabricCanvas();
    loadObj();
});

document.addEventListener('DOMContentLoaded', function() {
    const colorInputs = document.querySelectorAll('input[type="color"]');
    console.log('Found color inputs:', colorInputs.length);
    colorInputs.forEach(input => {
        const newInput = input.cloneNode(true);
        input.parentNode.replaceChild(newInput, input);
    });
    
    document.querySelectorAll('input[type="color"]').forEach(input => {
        input.addEventListener('input', function(e) {
            const partId = this.getAttribute('data-part');
            const color = e.target.value;
            console.log('Color change triggered for:', partId, 'with color:', color);
            console.log({
                element: this,
                partId: partId,
                color: color,
                dataAttributes: this.dataset
            });
            
            setColor(color, partId);
        });
        input.addEventListener('change', function(e) {
            const partId = this.getAttribute('data-part');
            const color = e.target.value;
            console.log('Color change event triggered for:', partId, 'with color:', color);
            setColor(color, partId);
        });
    });
});

function loadSvgTexture() {
    $.get(svgPath, function(data) {
        console.log('SVG loaded:', data);
        var svgData = new XMLSerializer().serializeToString(data.documentElement);
        console.log('SVG elements:', data.getElementsByTagName('*'));
        
        var tempDiv = document.createElement('div');
        tempDiv.style.display = 'none';
        tempDiv.innerHTML = svgData;
        document.body.appendChild(tempDiv);

        var svgElement = tempDiv.querySelector('svg');
        var canvas = document.createElement('canvas');
        var ctx = canvas.getContext('2d');
        
        canvas.width = 1024;
        canvas.height = 1024;

        var img = new Image();
        var svgBlob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
        var url = URL.createObjectURL(svgBlob);

        img.onload = function() {
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            
            if (currentTexture) {
                currentTexture.dispose();
            }
            currentTexture = new THREE.CanvasTexture(canvas);
            
            if (object) {
                object.traverse(function(child) {
                    if (child.isMesh) {
                        child.material.map = currentTexture;
                        child.material.needsUpdate = true;
                    }
                });
            }

            URL.revokeObjectURL(url);
            document.body.removeChild(tempDiv);
        };

        img.src = url;
    });
}

function init() {
    container = document.getElementById('model-container');
    if (!container) {
        console.error('model-container not found in the DOM.');
        return;
    }

    scene = new THREE.Scene();

    // Camera setup
    var screenRate = width / height;
    camera = new THREE.PerspectiveCamera(30, screenRate, 100, 1200);
    camera.position.set(250, 0, 0);
    scene.add(camera);

    // Controls setup
    controls = new THREE.OrbitControls(camera, container);
    controls.minDistance = 200;
    controls.maxDistance = 700;
    controls.update();

    // Renderer setup
    renderer = new THREE.WebGLRenderer({ 
        antialias: true, 
        alpha: true 
    });
    renderer.setPixelRatio(pixelRatio);
    renderer.setSize(width, height);
    renderer.setClearColor(new THREE.Color('skyblue'));
    renderer.gammaInput = true;
    renderer.gammaOutput = true;
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.soft = true;

    container.appendChild(renderer.domElement);

    // Add window resize handler
    window.addEventListener('resize', onWindowResize, false);

    // Start animation loop
    animate();
}

function setupLights() {
    scene.add(new THREE.AmbientLight(0x666666));

    var lights = [
        {color: 0xffffff, intensity: 0.53, position: {x: -500, y: 320, z: 500}},
        {color: 0xffffff, intensity: 0.3, position: {x: 200, y: 50, z: 500}},
        {color: 0xffffff, intensity: 0.4, position: {x: 0, y: 100, z: -500}},
        {color: 0xffffff, intensity: 0.3, position: {x: 1, y: 0, z: 0}},
        {color: 0xffffff, intensity: 0.3, position: {x: -1, y: 0, z: 0}}
    ];

    lights.forEach(function(lightData) {
        var dlight = new THREE.DirectionalLight(lightData.color, lightData.intensity);
        dlight.position.set(lightData.position.x, lightData.position.y, lightData.position.z);
        dlight.lookAt(0, 0, 0);
        scene.add(dlight);
    });

    // Add shadow-casting light
    var shadowLight = new THREE.DirectionalLight(0xdfebff, 0.3);
    shadowLight.position.set(500, 100, 80);
    shadowLight.castShadow = true;
    shadowLight.shadow.mapSize.width = 1024;
    shadowLight.shadow.mapSize.height = 1024;

    var d = 300;
    shadowLight.shadow.camera.left = -d;
    shadowLight.shadow.camera.right = d;
    shadowLight.shadow.camera.top = d;
    shadowLight.shadow.camera.bottom = -d;
    shadowLight.shadow.camera.far = 100;
    
    scene.add(shadowLight);
}

function loadObj() {
    var loader = new THREE.OBJLoader2();
    var onProgress = function(xhr) {
        if (xhr.lengthComputable) {
            var percentComplete = xhr.loaded / xhr.total * 100;
            console.log(Math.round(percentComplete, 2) + '% downloaded');
        }
    };

    var onError = function(xhr) {
        console.error(xhr);
    };

    loader.load('assets/shirt2.obj', function(data) {
        if (object) {
            scene.remove(object);
        }
        object = data.detail.loaderRootNode;
        
        // Apply initial material
        object.traverse(function(child) {
            if (child.isMesh) {
                child.material = new THREE.MeshPhongMaterial({
                    color: 0xffffff,
                    map: currentTexture
                });
            }
        });

        var scale = height / 5;
        object.scale.set(scale, scale, scale);
        object.position.set(0, -scale * 1.25, 0);
        object.rotation.set(0, Math.PI / 2, 0);
        
        object.receiveShadow = true;
        object.castShadow = true;
        
        scene.add(object);

        loadSvgTexture();
    }, onProgress, onError);
}

function onWindowResize() {
    var container = document.getElementById('model-container');
    width = container.clientWidth;
    height = container.clientHeight;

    camera.aspect = width / height;
    camera.updateProjectionMatrix();
    renderer.setSize(width, height);

    if (object) {
        var scale = height / 5;
        object.scale.set(scale, scale, scale);
        object.position.set(0, -scale * 1.25, 0);
    }
}

window.addEventListener('resize', onWindowResize, false);

onWindowResize();

function animate() {
    requestAnimationFrame(animate);
    controls.update();
    renderer.render(scene, camera);
}

function setColor(color, materialId) {
    console.log('setColor called with:', {
        color: color,
        materialId: materialId,
        type: typeof color,
        validColor: /^#[0-9A-F]{6}$/i.test(color)
    });
    
    selectedMaterial = materialId;
    
    update_svg('color', color);
    
    fabricCanvas.getObjects().forEach(obj => {
        if (obj.id === materialId) {
            obj.set('fill', color);
            fabricCanvas.renderAll();
        } else if (obj._objects) {
            obj._objects.forEach(groupObj => {
                if (groupObj.id === materialId) {
                    groupObj.set('fill', color);
                    fabricCanvas.renderAll();
                }
            });
        }
    });

    colorState[materialId] = color;
}

function initSvgCache() {
    fetch(svgPath)
        .then(response => response.text())
        .then(svgContent => {
            const parser = new DOMParser();
            cachedSvgDoc = parser.parseFromString(svgContent, 'image/svg+xml');
            console.log('SVG cached successfully');
        })
        .catch(error => console.error('Error caching SVG:', error));
}

initSvgCache();

function update_svg(op, value) {
    if (op === 'color' && selectedMaterial && cachedSvgDoc) {
        // Update color state
        colorState[selectedMaterial] = value;
        console.log('Updated color state:', colorState);

        // Get the element from cached SVG
        const svgElement = cachedSvgDoc.getElementById(selectedMaterial);
        
        if (svgElement) {
            console.log('Found element:', selectedMaterial);
            
            // Update the style
            const currentStyle = svgElement.getAttribute('style');
            const styleObj = currentStyle.split(';')
                .reduce((acc, style) => {
                    if (style.trim()) {
                        const [property, val] = style.split(':');
                        acc[property.trim()] = val.trim();
                    }
                    return acc;
                }, {});
            
            styleObj['fill'] = value;
            
            const newStyle = Object.entries(styleObj)
                .map(([prop, val]) => `${prop}:${val}`)
                .join(';');
            
            svgElement.setAttribute('style', newStyle);
            const svgString = new XMLSerializer().serializeToString(cachedSvgDoc);
            const svgUrl = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgString);
            
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                canvas.width = 4096;
                canvas.height = 4096;
                const ctx = canvas.getContext('2d');
                
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                
                if (currentTexture) {
                    currentTexture.dispose();
                }
                
                currentTexture = new THREE.CanvasTexture(canvas);
                currentTexture.needsUpdate = true;
                
                if (object) {
                    object.traverse(function(child) {
                        if (child.isMesh) {
                            child.material.map = currentTexture;
                            child.material.needsUpdate = true;
                        }
                    });
                }
                
                renderer.render(scene, camera);
            };
            
            img.src = svgUrl;
        } else {
            console.error('Element not found:', selectedMaterial);
        }
    }
}

function debugTexture() {
    if (currentTexture && currentTexture.image) {
        const debugImg = document.createElement('img');
        debugImg.src = currentTexture.image.toDataURL();
        debugImg.style.position = 'fixed';
        debugImg.style.top = '10px';
        debugImg.style.left = '10px';
        debugImg.style.width = '200px';
        debugImg.style.border = '2px solid red';
        document.body.appendChild(debugImg);
    }
}


function initFabricCanvas() {
    const canvasElement = document.getElementById('fabric-canvas');
    fabricCanvas = new fabric.Canvas('fabric-canvas', {
        width: 700,
        height: 800,
        backgroundColor: 'rgba(255, 255, 255, 0)',  
        selection: true  
    });
    
    document.getElementById('image-upload').addEventListener('change', handleImageUpload);

    fabricCanvas.on('object:modified', function() {
        console.log('Object modified');
        updateThreeJsTexture();
    });

    fabricCanvas.on('text:changed', function() {
        console.log('Text changed');
        updateThreeJsTexture();
    });
    fetch(svgPath)
    .then(response => response.text())
    .then(svgString => {
        fabric.loadSVGFromString(svgString, function(objects, options) {
            const svgGroup = fabric.util.groupSVGElements(objects, options);
            svgGroup.scaleToWidth(fabricCanvas.width);
            svgGroup.center();
            svgGroup.set('selectable', false);
            fabricCanvas.add(svgGroup);
            fabricCanvas.renderAll();
            updateThreeJsTexture();
        });
    })
    .catch(error => console.error('Error loading SVG:', error));
}

function uploadImage() {
    const fileInput = document.getElementById('image-upload');
    fileInput.click();
}

function handleImageUpload(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(f) {
            const img = new Image();
            img.onload = function() {
                const fabricImage = new fabric.Image(img, {
                    left: fabricCanvas.width / 2 - img.width / 2,
                    top: fabricCanvas.height / 2 - img.height / 2,
                    scaleX: fabricCanvas.width / img.width,
                    scaleY: fabricCanvas.height / img.height
                });
                fabricCanvas.add(fabricImage);
                fabricCanvas.renderAll();
                updateThreeJsTexture();
            };
            img.src = f.target.result;
        };
        reader.readAsDataURL(file);
    }
}

function updateThreeJsTexture() {
    if (!fabricCanvas) {
        console.error('Fabric canvas not initialized');
        return;
    }

    const textureImage = fabricCanvas.toDataURL({
        format: 'png',
        quality: 1,
        multiplier: 4  // Higher resolution
    });
    
    const loader = new THREE.TextureLoader();
    loader.load(textureImage, function(texture) {
        if (currentTexture) {
            currentTexture.dispose();
        }
        
        currentTexture = texture;
        currentTexture.needsUpdate = true;
        texture.minFilter = THREE.LinearFilter;
        texture.magFilter = THREE.LinearFilter;
        texture.anisotropy = renderer.capabilities.getMaxAnisotropy();
        
        if (object) {
            object.traverse(function(child) {
                if (child.isMesh) {
                    child.material.map = currentTexture;
                    child.material.needsUpdate = true;
                }
            });
        }
        
        // Force a render update
        renderer.render(scene, camera);
    });
}

fabricCanvas.on('selection:created', updateFormFromText);
fabricCanvas.on('selection:updated', updateFormFromText);

// Function to update form when text is selected
function updateFormFromText(e) {
    const selectedObject = e.selected[0];
    if (selectedObject && selectedObject.type === 'text') {
        document.getElementById('text-input').value = selectedObject.text;
        document.getElementById('text-color').value = selectedObject.fill;
        document.getElementById('font-family').value = selectedObject.fontFamily;
        document.getElementById('font-size').value = selectedObject.fontSize;
    }
}

// Your modified addTextToCanvas function
function addTextToCanvas() {
    const textInput = document.getElementById('text-input');
    const textColor = document.getElementById('text-color');
    const fontFamily = document.getElementById('font-family');
    const fontSize = document.getElementById('font-size');

    // Get the currently selected object
    const activeObject = fabricCanvas.getActiveObject();

    if (!textInput.value && !activeObject) {
        console.log('No text entered');
        return;
    }

    // If there's a selected object, just update its properties
    if (activeObject && activeObject.type === 'text') {
        console.log('Updating existing text:', {
            color: textColor.value,
            font: fontFamily.value,
            size: fontSize.value
        });

        activeObject.set({
            text: textInput.value || activeObject.text, // Keep existing text if input is empty
            fill: textColor.value,
            fontFamily: fontFamily.value,
            fontSize: parseInt(fontSize.value, 10)
        });

        fabricCanvas.renderAll();
        updateThreeJsTexture();
        return;
    }

    // If no active object, create new text
    console.log('Adding new text:', {
        text: textInput.value,
        color: textColor.value,
        font: fontFamily.value,
        size: fontSize.value
    });

    const fabricText = new fabric.Text(textInput.value, {
        flipY: true,
        flipX: false,
        originX: 'center',
        originY: 'center',
        left: fabricCanvas.width / 2, // Center horizontally
        top: fabricCanvas.height / 2,
        fontSize: parseInt(fontSize.value, 10),
        fontFamily: fontFamily.value,
        fill: textColor.value,
        selectable: true,
        editable: true
    });

    fabricCanvas.add(fabricText);
    fabricCanvas.setActiveObject(fabricText);
    fabricCanvas.renderAll();
    updateThreeJsTexture();
    textInput.value = '';
}

// Also add this to handle color changes directly
textColor.addEventListener('input', function() {
    const activeObject = fabricCanvas.getActiveObject();
    if (activeObject && activeObject.type === 'text') {
        activeObject.set('fill', this.value);
        fabricCanvas.renderAll();
        updateThreeJsTexture();
    }
});

// Similarly for font and size
fontFamily.addEventListener('change', function() {
    const activeObject = fabricCanvas.getActiveObject();
    if (activeObject && activeObject.type === 'text') {
        activeObject.set('fontFamily', this.value);
        fabricCanvas.renderAll();
        updateThreeJsTexture();
    }
});

fontSize.addEventListener('input', function() {
    const activeObject = fabricCanvas.getActiveObject();
    if (activeObject && activeObject.type === 'text') {
        activeObject.set('fontSize', parseInt(this.value, 10));
        fabricCanvas.renderAll();
        updateThreeJsTexture();
    }
});


function addToCart() {
    captureModelSnapshots().then(snapshots => {
        const formData = new FormData();
        formData.append('action', 'upload_snapshots');
        
        // Convert snapshots to the format expected by PHP
        const processedSnapshots = {};
        snapshots.forEach((snapshot) => {
            processedSnapshots[snapshot.view] = snapshot.imag;
        });

        // Add the processed snapshots to formData
        formData.append('snapshots', JSON.stringify(processedSnapshots));

        // Upload snapshots
        $.ajax({
            url: 'uploads.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Upload response:', response);
                if (response.status === 'success' || response.status === 'partial') {
                    // Redirect to cart page
                    window.location.href = 'cart_3d.php';
                } else {
                    console.error('Upload failed:', response.message);
                    alert('Error adding item to cart. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Upload error:', error);
                console.error('Server response:', xhr.responseText);
                alert('Error uploading snapshots. Please try again.');
            }
        });
    }).catch(error => {
        console.error('Capture error:', error);
        alert('Error capturing model views. Please try again.');
    });
}
// Helper function to convert base64 to Blob
function dataURItoBlob(dataURI) {
    const byteString = atob(dataURI.split(',')[1]);
    const mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
    const arrayBuffer = new ArrayBuffer(byteString.length);
    const uint8Array = new Uint8Array(arrayBuffer);
    
    for (let i = 0; i < byteString.length; i++) {
        uint8Array[i] = byteString.charCodeAt(i);
    }

    return new Blob([uint8Array], { type: mimeString });
}

async function captureModelSnapshots() {
    let fileName = await showCustomModal("Enter a name for your zip file:", "model_snapshots");

    if (!fileName.toLowerCase().endsWith('.zip')) {
        fileName += '.zip';
    }
    console.log('Captured file name:', fileName);

    const positions = [
        { view: 'right', position: { x: 0, y: 0, z: 250 }, rotation: { x: 0, y: Math.PI, z: 0 } },
        { view: 'left', position: { x: 0, y: 0, z: -250 }, rotation: { x: 0, y: 0, z: 0 } },
        { view: 'back', position: { x: -250, y: 0, z: 0 }, rotation: { x: 0, y: Math.PI / 2, z: 0 } },
        { view: 'front', position: { x: 250, y: 0, z: 0 }, rotation: { x: 0, y: -Math.PI / 2, z: 0 } }
    ];

    const originalPosition = { ...camera.position };
    const originalRotation = { ...camera.rotation };
    const originalControlsEnabled = controls.enabled;

    controls.enabled = false;

    const zip = new JSZip();

    for (const pos of positions) {
        camera.position.set(pos.position.x, pos.position.y, pos.position.z);
        camera.rotation.set(pos.rotation.x, pos.rotation.y, pos.rotation.z);

        await new Promise(resolve => setTimeout(resolve, 100));

        renderer.render(scene, camera);

        const imageData = renderer.domElement.toDataURL('image/png').split(',')[1];
        zip.file(`${pos.view}_snapshot.png`, imageData, { base64: true });
    }

    camera.position.set(originalPosition.x, originalPosition.y, originalPosition.z);
    camera.rotation.set(originalRotation.x, originalRotation.y, originalRotation.z);
    controls.enabled = originalControlsEnabled;
    renderer.render(scene, camera);

    const zipContent = await zip.generateAsync({ type: 'blob' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(zipContent);
    link.download = fileName; // Use the fileName from the modal
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
}

function showCustomModal(message, defaultValue) {
    return new Promise((resolve) => {
        // Create modal elements
        const modal = document.createElement('div');
        const modalContent = document.createElement('div');
        const messageElement = document.createElement('p');
        const input = document.createElement('input');
        const buttonContainer = document.createElement('div');
        const cancelButton = document.createElement('button');
        const okButton = document.createElement('button');

        // Set up modal content
        messageElement.textContent = message;
        input.value = defaultValue;
        cancelButton.textContent = 'Cancel';
        okButton.textContent = 'OK';

        // Append elements
        modalContent.appendChild(messageElement);
        modalContent.appendChild(input);
        buttonContainer.appendChild(cancelButton);
        buttonContainer.appendChild(okButton);
        modalContent.appendChild(buttonContainer);
        modal.appendChild(modalContent);
        document.body.appendChild(modal);

        // Style the modal (same as previous example)
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
        modal.style.display = 'flex';
        modal.style.justifyContent = 'center';
        modal.style.alignItems = 'center';

        modalContent.style.backgroundColor = 'white';
        modalContent.style.padding = '20px';
        modalContent.style.borderRadius = '8px';
        modalContent.style.textAlign = 'center';
        modalContent.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';

        input.style.width = '80%';
        input.style.padding = '10px';
        input.style.margin = '10px 0';
        input.style.border = '1px solid #ccc';
        input.style.borderRadius = '4px';

        buttonContainer.style.display = 'flex';
        buttonContainer.style.justifyContent = 'space-between';

        cancelButton.style.padding = '10px 20px';
        cancelButton.style.border = 'none';
        cancelButton.style.backgroundColor = '#007BFF';
        cancelButton.style.color = 'white';
        cancelButton.style.borderRadius = '4px';
        cancelButton.style.cursor = 'pointer';

        okButton.style.padding = '10px 20px';
        okButton.style.border = 'none';
        okButton.style.backgroundColor = '#007BFF';
        okButton.style.color = 'white';
        okButton.style.borderRadius = '4px';
        okButton.style.cursor = 'pointer';

        // Event listeners for buttons
        cancelButton.addEventListener('click', function () {
            document.body.removeChild(modal);
            resolve(null); // Resolve with null if canceled
        });

        okButton.addEventListener('click', function () {
            const fileName = input.value.trim();
            document.body.removeChild(modal);
            resolve(fileName || defaultValue); // Resolve with user input or default value
        });
    });
}



// If you need to upload to server as well
function addToCart() {
    captureModelSnapshots().then(snapshots => {
        const formData = new FormData();
        formData.append('action', 'upload_snapshots');
        
        // Convert snapshots to the format expected by PHP
        const processedSnapshots = {};
        snapshots.forEach((snapshot) => {
            if (snapshot && snapshot.imag) {
                processedSnapshots[snapshot.view] = snapshot.imag;
            }
        });

        // Only proceed if we have at least one valid snapshot
        if (Object.keys(processedSnapshots).length === 0) {
            alert('Error: No valid snapshots were captured. Please try again.');
            return;
        }

        // Add the processed snapshots to formData
        formData.append('snapshots', JSON.stringify(processedSnapshots));

        // Upload snapshots
        $.ajax({
            url: 'uploads.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Upload response:', response);
                if (response.status === 'success' || response.status === 'partial') {
                    // Redirect to cart page
                    window.location.href = 'cart_3d.php';
                } else {
                    console.error('Upload failed:', response.message);
                    alert('Error adding item to cart. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Upload error:', error);
                console.error('Server response:', xhr.responseText);
                alert('Error uploading snapshots. Please try again.');
            }
        });
    }).catch(error => {
        console.error('Capture error:', error);
        alert('Error capturing model views. Please try again.');
    });
}
