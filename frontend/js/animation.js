class Animation {
    constructor () {
        this.queue = []; // file d'attente des fonctions d'animation
        this.isProcessing = false; // indique si une animation est actuellement en cours
        this.onQueueStart = null; // déclenché quand une séqunece d'animation commence
        this.onQueueEnd = null; // déclenché quand toutes les animations sont terminnee
    }

    // ajout d'une animation à la queue et demarrage du traitmenet si nécéssaire
    enqueue (animationTask) {
        if (typeof animationTask !== "function") {
            console.error("élément ajouté à la queue d'animation doit être une fonction");
            return;
        }

        this.queue.push(animationTask);

        if (!this.isProcessing) { this.processNext(); }
    }

    // dépilé et exécuton séquentiellement chaque animation
    async processNext () {
        if (this.queue.length === 0) {
            this.isProcessing = false;
            if (typeof this.onQueueEnd === "function") { this.onQueueEnd(); }
            return;
        }

        if (!this.isProcessing) {
            this.isProcessing = true;
            if (typeof this.onQueueStart === "function") { this.onQueueStart(); }
            return;
        }

        // extraction de la premiere animaton
        const currentAnimation = this.queue.shift();
        try {
            await currentAnimation();
        } catch (error) {
            console.error(`ERREUR PENDANT EXECUTION - ${error}`);
        }

        this.processNext();
    }

    // si animation est en cours ou en attente... retourne vrai
    isBusy () { return this.isProcessing || this.queue.length > 0; }
}

const globalAnimation = new Animation();
globalAnimation.onQueueStart = () => {
    document.body.classList.add("ui-locked");
    console.log("INTERFACE VERROUILLE PENDANT L'ANIMATION");
}

globalAnimation.onQueueEnd = () => {
    document.body.classList.remove("ui-locked");
    console.log("INTERFACE DEVERROUILLE");
}

// animation de la capture d'un pion
function animateCapturePion (pionCaptureInfo, startCoords, homeCoords, duree = 380) {
    return new Promise((resolve) => {
        const pionId = `pion-${pionCaptureInfo.couleur.toLowerCase()}-${pionCaptureInfo.index}`;
        const pionElement = document.getElementById(pionId);
        if (!pionElement) { resolve(); return; }

        // PHASE 1 - impact et secousse
        pionElement.classList.add("animation-impact-capture");
        setTimeout(() => {
            pionElement.classList.remove("animation-impact-capture");
            pionElement.classList.add("pion-ejecte");

            // PHASE 2 - éjection avec vitesse
            const startTime = performance.now();
            const heightArcMax = 35;

            function animateFlying (actualTIme) {
                const finishTIme = actualTIme - startTime;
                let progression = Math.min(finishTIme / duree, 1.0);

                // accelaration quadratique (progression * progression)
                const progressionEased = progression * progression;

                // calcul des coordonnées avec interpolation lineaire
                const xCourant = interpolationLineaire(startCoords.x, homeCoords.x);
                let yCourant = interpolationLineaire(startCoords.y, homeCoords.y);

                // effet d'arc parabolique inverse
                const offsetArc = Math.sin(progression * Math.PI) * heightArcMax;
                yCourant -= offsetArc;

                pionElement.setAttribute("transform", `translate (${xCourant}, ${yCourant})`);

                if (progression < 1.0) { requestAnimationFrame(animateFlying); }
                else {
                    // PHASE 3 - arrivée à la maison de l'adeversaire
                    pionElement.classList.remove("pion-ejecte");
                    pionElement.setAttribute("data-etat", "MAISON");
                    resolve();
                }
            }
        }, 350);
    });
}

// animation d'avertissement visuel sur un pion fautif
function animatePenaliteFaute (pionInfo, duree = 1200) {
    return new Promise( (resolve) => {
        const pionId = `pion-${pionInfo.couleur.toLowerCase()}-${pionInfo.indexPi}`;
        const pionElement = document.getElementById(pionId);
        if (!pionElement) { resolve(); return; }

        pionElement.classList.add("animation-penalite-flash");
        setTimeout(() => {
            pionElement.classList.remove("animation-penalite-flash");
            resolve();
        }, duree);
    })
}

// animation de la sortie d'un pion depuis sa maison
function animateEffetSortieMaison (pionInfo, startCoordsCircuit) {
    return new Promise((resolve) => {
        const pionId = `pion-${pionInfo.couleur.toLowerCase()}-${pionInfo.indexPI}`;
        const pionElement = document.getElementById(pionId);
        if (!pionElement) { resolve(); return; }

        // positionnement du pion sur la case de depart
        pionElement.setAttribute("transform", `translate(${startCoordsCircuit.x}, ${startCoordsCircuit.y})`);
        pionElement.classList.add("animation-pop-sortie");
        setTimeout(() => {
            pionElement.classList.remove("animation-pop-sortie");
            resolve();
        }, 500);
    });
}

// génération d'une exploision de particules de confetti
function generateConfettis (xCentre, yCentre, svg) {
    const nombreParticules = 30;
    const couleurs = ["#e74c3c", "#3498db", "#f1c40f", "#2ecc71", "#9b59b6", "#fff"];

    // groupe conteneur temporaire pour les confettis
    const confettiList = document.createElementNS(SVG_NS, "g");
    confettiList.setAttribute("id", "confetti-list");
    svg.appendChild(confettiList);

    for (let i = 0; i < nombreParticules; i++) {
        const particule = document.createElementNS(SVG_NS, "circle");
        // couleur et taille
        const couleur = couleurs[Math.floor(Math.random() * couleurs.length)];
        const rayon = Math.random() * 3 + 2;
        // angle et distance aléatoire
        const angle = Math.random() * Math.PI * 2;
        const distance = Math.random() * 80 + 40;

        const dx = Math.cos(angle) * distance;
        const dy = Math.sin(angle) * distance;

        particule.setAttribute("cx", xCentre);
        particule.setAttribute("cy", yCentre);
        particule.setAttribute("r", rayon);
        particule.setAttribute("fill", couleur);
        particule.setAttribute("class", "confetti-particule");

        // transmission des variables de trajectoire
        particule.style.setProperty("--dx", `${dx}px`);
        particule.style.setProperty("--dy", `${dy}px`);

        confettiList.appendChild(particule);
        // nettoyage automatique du DOM après l'animation
        setTimeout(() => {
            if (confettiList.parentNode) { confettiList.parentNode.removeChild(confettiList); }
        }, 1300);
    }
}

// déclenchement la celebration de victoire lorsqu'un pion rentre au CENTRE
function animateVictoireCentre (pionInfo, svg) {
    const pionId = `pion-${pionInfo.couleur.toLowerCase()}-${pionInfo.indexPi}`;
    const pionElement = document.getElementById(pionId);

    // obtention le centre exact du plateau
    coordsCentre = getCielPixelCoords(pionInfo.couleur);
    if (pionElement) { pionElement.classList.add("animation-victoire-centre"); }

    generateConfettis(coordsCentre.x, coordsCentre.y, svg);
}
