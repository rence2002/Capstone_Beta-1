
window.addEventListener("DOMContentLoaded", function () {
    // Check if the WebXR API is supported
    if (navigator.xr) {
        navigator.xr.isSessionSupported("immersive-ar").then(async (supported) => {
            if (supported) {
                // Show the canvas and hide the info message if AR is supported
                document.getElementById("renderCanvas").style.display = "block";

                document.getElementById("info-message").style.display = "none";
                
                var canvas = document.getElementById("renderCanvas");
                var engine = new BABYLON.Engine(canvas, true); // Create the Babylon.js engine
                var createScene = async function () {
                    var scene = new BABYLON.Scene(engine); // Create a new Babylon.js scene
                    var camera = new BABYLON.FreeCamera(
                        "myCamera",
                        new BABYLON.Vector3(0, 1, -5),
                        scene
                    ); // Create a camera

           

                    var ui = BABYLON.GUI.AdvancedDynamicTexture.CreateFullscreenUI("myUI");

                    // Create a container for the UI elements
                    var container = new BABYLON.GUI.StackPanel();
                    container.width = "300px";
                    container.height = "200px";
                    container.horizontalAlignment = BABYLON.GUI.Control.HORIZONTAL_ALIGNMENT_LEFT;
                    container.verticalAlignment = BABYLON.GUI.Control.VERTICAL_ALIGNMENT_TOP;
                    container.top = "20px";
                    container.left = "20px";
                    
                    var renderScale = 1.0;
                    var hardwareScalingLevel = 0.5;
                    
                    engine.setHardwareScalingLevel(hardwareScalingLevel); // Set hardware scaling
                    ui.renderScale = renderScale;
                    
                    // Create and style a GUI text block for measurements
                    var guiText = new BABYLON.GUI.TextBlock();
                    guiText.text = "";
                    guiText.height = `${(200 * renderScale) / hardwareScalingLevel}px`;
                    guiText.color = "white";
                    guiText.fontSize = (32 * renderScale) / hardwareScalingLevel;
                    guiText.verticalAlignment =
                        BABYLON.GUI.Control.VERTICAL_ALIGNMENT_TOP;
                    guiText.textHorizontalAlignment =
                        BABYLON.GUI.Control.HORIZONTAL_ALIGNMENT_LEFT;
                    guiText.horizontalAlignment =
                        BABYLON.GUI.Control.HORIZONTAL_ALIGNMENT_LEFT;
                    guiText.top = `${(50 * renderScale) / hardwareScalingLevel}px`;
                    guiText.left = `${(50 * renderScale) / hardwareScalingLevel}px`;
                    ui.addControl(guiText);
                    
                   
                    
                    // Set camera target and attach control to canvas
                    camera.setTarget(BABYLON.Vector3.Zero());
                    camera.attachControl(canvas, true);
                    
                    // Create a light source
                    var light = new BABYLON.HemisphericLight(
                        "light",
                        new BABYLON.Vector3(0, 5, 0),
                        scene
                    );
                    light.diffuse = BABYLON.Color3.White();
                    light.intensity = 0.1;
                    light.specular = new BABYLON.Color3(0, 0, 0);
                    
                    // Create the XR experience
                    var xr = await scene.createDefaultXRExperienceAsync({
                        optionalFeatures: true,
                        disableDefaultUI: true,
                    });

                    // Create a button to start measuring
                    var enterXRButton = BABYLON.GUI.Button.CreateSimpleButton(
                        "enterXRButton",
                        "CLICK ANYWHERE TO START!"
                    );
                    enterXRButton.width = `${
                        (300 * renderScale) / hardwareScalingLevel
                    }px`;
                    enterXRButton.height = `${
                        (50 * renderScale) / hardwareScalingLevel
                    }px`;
                    enterXRButton.color = "black";
                    enterXRButton.fontSize = (20 * renderScale) / hardwareScalingLevel;
                    // Set the background to semi-transparent white
                    enterXRButton.background = "#B99470"; // 0.5 is 50% opacity
                    enterXRButton.height = "100vh";
                    enterXRButton.width = "100vw";
                    enterXRButton.fontFamily = "roboto"; // Specify your custom font here
                    enterXRButton.isVisible = true;
                  
                    
                    
                    var pairs = []; // Store measurement pairs

                    // Handle XR state changes
                    xr.baseExperience.onStateChangedObservable.add((state) => {
                        if (state === BABYLON.WebXRState.ENTERING_XR) {
                            // Logging camera position can be added here
                        } else if (state === BABYLON.WebXRState.IN_XR) {
                            enterXRButton.isVisible = false; // Hide button when in XR
                        } else if (state === 3) { // State 3 might be a custom state
                            enterXRButton.isVisible = true;
                        }
                    });



                    // Enable XR features
                    const fm = xr.baseExperience.featuresManager;
                    fm.enableFeature(BABYLON.WebXRBackgroundRemover);
                    const hitTest = fm.enableFeature(BABYLON.WebXRHitTest, "latest");
                    const anchorSystem = fm.enableFeature(
                        BABYLON.WebXRAnchorSystem,
                        "latest"
                    );

                    // Create a dot for positioning measurements
                    const dot = BABYLON.SphereBuilder.CreateSphere(
                        "dot",
                        {
                            diameter: 0.05,
                        },
                        scene
                    );
                    dot.rotationQuaternion = new BABYLON.Quaternion();
                    dot.material = new BABYLON.StandardMaterial("dot", scene);
                    dot.material.emissiveColor = BABYLON.Color3.Green();
                    dot.isVisible = false;

                    let lastHitTest = null;
                    let currentPair = null;
                    let anchorsAvailable = false;

                    // Handle hit test results
                    hitTest.onHitTestResultObservable.add((results) => {
                        if (results.length) {
                            dot.isVisible = true;
                            results[0].transformationMatrix.decompose(
                                dot.scaling,
                                dot.rotationQuaternion,
                                dot.position
                            );
                            lastHitTest = results[0];
                            if (currentPair) {
                                if (currentPair.line) {
                                    currentPair.line.dispose();
                                }
                                // Draw a line between start and end dots
                                currentPair.line = BABYLON.Mesh.CreateLines(
                                    "lines",
                                    [currentPair.startDot.position, dot.position],
                                    scene
                                );
                                const dist = BABYLON.Vector3.Distance(
                                    currentPair.startDot.position,
                                    dot.position
                                );
                                let roundDist = Math.round(dist * 100) / 100;
                                currentPair.text.text = roundDist + "m";
                                guiText.text =
                                    "Last Measure:\n" +
                                    roundDist +
                                    "m\n" +
                                    Math.round((roundDist / 0.3048) * 100) / 100 +
                                    "ft\n" +
                                    Math.round(roundDist * 39.37 * 100) / 100 +
                                    "in\n" +
                                    Math.round(roundDist * 100 * 100) / 100 +
                                    "cm";
                            }
                        } else {
                            lastHitTest = null;
                            dot.isVisible = false;
                            guiText.text = "";
                        }
                        
                    });

                    // Process click for measurement
                    const processClick = () => {
                        const newDot = dot.clone("newDot");
                        if (!currentPair) {
                            const label = new BABYLON.GUI.Rectangle("label");
                            label.background = "black";
                            label.height = "60px";
                            label.alpha = 0.5;
                            label.width = "200px";
                            label.cornerRadius = 20;
                            label.thickness = 1;
                            label.zIndex = 5;
                            label.top = -30;
                            ui.addControl(label);

                            const text = new BABYLON.GUI.TextBlock();
                            text.color = "white";
                            text.fontSize = "36px";
                            label.addControl(text);
                            currentPair = {
                                startDot: newDot,
                                label,
                                text,
                            };
                        } else {
                            currentPair.label.linkWithMesh(newDot);
                            currentPair.endDot = newDot;
                            pairs.push(currentPair);
                            currentPair = null;
                        }
                        return newDot;
                    };

                    // Handle anchor addition
                    anchorSystem.onAnchorAddedObservable.add((anchor) => {
                        anchor.attachedNode = processClick();
                    });

                    // Handle XR button click
                    enterXRButton.onPointerUpObservable.add(async function () {
                        const session = await xr.baseExperience.enterXRAsync(
                            "immersive-ar",
                            "unbounded",
                            xr.renderTarget
                        );

                        // Add event listener for pointer clicks in XR
                        scene.onPointerObservable.add(async (eventData) => {
                            if (lastHitTest) {
                                if (lastHitTest.xrHitResult.createAnchor) {
                                    const anchor = await anchorSystem.addAnchorPointUsingHitTestResultAsync(
                                        lastHitTest
                                    );
                                } else {
                                    processClick();
                                }
                            }
                        }, BABYLON.PointerEventTypes.POINTERDOWN);
                    });

                    // Update lines on XR frame
                    xr.baseExperience.sessionManager.onXRFrameObservable.add(() => {
                        pairs.forEach((pair) => {
                            pair.line.dispose();
                            pair.line = BABYLON.Mesh.CreateLines(
                                "lines",
                                [pair.startDot.position, pair.endDot.position],
                                scene
                            );
                        });
                    });

                    // Add the measure button to the UI
            
                    ui.addControl(enterXRButton);

                    // Create the exit button
                    const exitButton = new BABYLON.GUI.Button.CreateSimpleButton("exitButton", "Exit");
                    exitButton.width = "200px";
                    exitButton.height = "60px";
                    exitButton.color = "white";
                    exitButton.background = "red";
                    exitButton.cornerRadius = 10;
                    exitButton.fontSize = "24px";
                    exitButton.thickness = 1;
                    exitButton.zIndex = 5;
                    
                    // Position the button at the top left
                    exitButton.horizontalAlignment = BABYLON.GUI.Control.HORIZONTAL_ALIGNMENT_LEFT;
                    exitButton.verticalAlignment = BABYLON.GUI.Control.VERTICAL_ALIGNMENT_TOP;
                    exitButton.left = "500px";  // Adjust this value to fine-tune horizontal position
                    exitButton.top = "100px";   // Adjust this value to fine-tune vertical position
                    
                    exitButton.onPointerUpObservable.add(() => {
                        // Redirect to gallery.html
                        window.location.href = "/html-files/Gallery.html";
                    });

                    // Add the exit button to the UI
                    ui.addControl(exitButton);

                    // Function to create the scene
                    return scene;

                };

                var scene = await createScene();

                // Run the render loop
                engine.runRenderLoop(function () {
                    scene.render();
                });

                // Handle window resize
                window.addEventListener("resize", function () {
                    engine.resize();
                });
            } else {
                // AR not supported on device
                document.getElementById("ar-image").style.display = "block";
                 document.getElementById("info-message").style.display = "block";
                document.getElementById("info-message").innerText =
                    "Unfortunately your device doesn't support Immersive AR of WebXR";
            }
        });
    } else {
        // WebXR API not supported
        document.getElementById("renderCanvas").style.display = "none";
        document.getElementById("info-message").style.display = "block";
        document.getElementById("info-message").innerText =
            "Unfortunately your device doesn't support WebXR";
    }


    
});

