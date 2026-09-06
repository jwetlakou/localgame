// positions des points (pips) dans la grille (3X3) pour chaque face du dé
const MARQUES_DICES = {
    1: [4],
    2: [0,  8],
    3: [0,  4,  8],
    4: [0,  2,  6,  8],
    5: [0,  2,  4,  6,  8],
    6: [0,  2,  3,  5,  6,  8]
};

let btnRollElement = null, diceCube1 = null, diceCube2 = null, indicatorJoueurElement = null;

// initialisation des composant des 2 dés et des écouteurs d'évènements
function initializeDiceComposants (auLancerTermine) {
    btnRollElement = document.getElementById("roll-dice-btn"), diceCube1 = document.getElementById("dice-1"), diceCube2 = document.getElementById("dice-2"), indicatorJoueurElement = document.getElementById("indicator-player");

    // rendu initial des deux dés sur la face 1
    displayDiceFace(diceCube1, 1);
    displayDiceFace(diceCube2, 1);

    if (btnRollElement) { btnRollElement.addEventListener("click", () => { rollAnimeDices(auLancerTermine); }); }
}

// dessination des points sur la face des dés
function displayDiceFace (diceCubeElement, valeur) {
    if (!diceCubeElement || typeof diceCubeElement.appendChild !== "function") { console.error(`l'element n'est pas fonction - ${diceCubeElement}`); return; }
    diceCubeElement.innerHTML = "";

    const positionValides = MARQUES_DICES[valeur] || [];

    for (let i = 0; i < 9; i++) {
        const cellule = document.createElement("div");
        if (positionValides.includes(i)) {
            const pip = document.createElement("div");
            pip.className = "dice-pip";
            cellule.appendChild(pip);
        }
        diceCubeElement.appendChild(cellule);
    }
}

// exécutions de l'animation de roulement simultanée des deux dés et renvoie les valeurs finales
function rollAnimeDices (callback) {
    // désactiver le bouton pendant l'animation
    if (!btnRollElement) return;
    btnRollElement.disabled = true;

    if (diceCube1) diceCube1.classList.add("roll-in");
    if (diceCube2) diceCube2.classList.add("roll-in");

    // application du defilement des faces aléatoires à grande vitesse
    const intervalleLancer = setInterval( () => {
        const face1 = Math.floor(Math.random() * 6) + 1;
        const face2 = Math.floor(Math.random() * 6) + 1;
        displayDiceFace(diceCube1, face1);
        displayDiceFace(diceCube2, face2);
    }, 80);
    
    // arrêt de l'animation après 800ms et fixation du resultat
    setTimeout( () => {
        clearInterval(intervalleLancer);
        if (diceCube1) diceCube1.classList.remove("roll-in");
        if (diceCube2) diceCube2.classList.remove("roll-in");

        // resultats finals réels (1 - 6)
        const resultat1 = Math.floor(Math.random() * 6) + 1;
        const resultat2 = Math.floor(Math.random() * 6) + 1;
        displayDiceFace(diceCube1, resultat1);
        displayDiceFace(diceCube2, resultat2);

        if (typeof callback === "function") { callback([resultat1, resultat2]); }
    }, 800);
}

// mise à jour le nom du joueur dont c'est le tour
function updateIndicatorTurn (couleurJoueur) {
    if (indicatorJoueurElement) { 
        indicatorJoueurElement.textContent = `tour : ${couleurJoueur.toLowerCase()}`;

        // couleurs de texte adaptées
        const couleurTextes = {
            ROUGE:  "#e74c3c",
            BLEU:   "#3498db",
            JAUNE:  "#f1c40f",
            VERT:   "#2ecc71",
        };

        indicatorJoueurElement.style.color = couleurTextes[couleurJoueur] || "#2c3e50";
    }
}

function authorizedLancer () {
    if (btnRollElement) { btnRollElement.disabled = false; }
}
