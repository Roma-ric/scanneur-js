<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Scanner de code QR</title>
</head>
<body> 
    <video id="video" width="640" height="480" autoplay></video>
    <canvas id="canvas" width="640" height="480" style="display:none;"></canvas>
    <div id="result"></div>
    <button id="stop">Arrêter la caméra</button>
    <form action="scan.php" method="post">
    <input type="text" name="recup" id="recup" value="ok">
    <button type="submit"> Envoyer</button>
    </form>
    <script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.min.js"></script>
    <script>
        const valQr = document.getElementById("recup");
        const video = document.getElementById("video");
        const canvasElement = document.getElementById("canvas");
        const canvas = canvasElement.getContext("2d");
        const resultContainer = document.getElementById("result");
        let scanning = false;
        let stream = null;

        // Demander l'autorisation d'utiliser la caméra
        navigator.mediaDevices.getUserMedia({ video: true, audio: false })
        .then(function(mediaStream) {
            stream = mediaStream;
            video.srcObject = stream;
            video.setAttribute("playsinline", true); // nécessaire sur iOS pour empêcher la lecture en plein écran
            video.play();
            requestAnimationFrame(tick);
        })
        .catch(function(err) {
            console.log("Erreur d'accès à la caméra:", err);
        });

        // Configurer la résolution de la vidéo pour améliorer la précision du scan
        const constraints = { 
            audio: false, 
            video: { 
                width: 640, 
                height: 480, 
                facingMode: 'environment' 
            } 
        };

        // Charger le son à jouer lorsqu'un code QR est détecté
        const audio = new Audio('bip.mp3');


        // Détecter et décoder le code QR dans le flux vidéo
        function tick() {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvasElement.height = video.videoHeight;
                canvasElement.width = video.videoWidth;
                canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
                const imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "dontInvert",
                });
                if (code && !scanning) {
                    scanning = true;
                    // Jouer le son pour signaler que le code QR a été détecté
                    audio.play();
                    resultContainer.innerText = "Contenu du code QR : " + code.data;
                    valQr.value = code.data;
                }
            }
            requestAnimationFrame(tick);
        }

        // Arrêter l'accès à la caméra
        const stopButton = document.getElementById("stop");
        stopButton.addEventListener("click", function() {
            stream.getTracks().forEach(function(track) {
                track.stop();
            });
            video.srcObject = null;
        });
    </script>
    <?php
     
    ?>
</body>
</html>
