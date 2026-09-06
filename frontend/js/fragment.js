const TEMPS_PAR_TOUR_SECONDES = 30;
let timeRestant = TEMPS_PAR_TOUR_SECONDES, timerInterval = null, callbackEndTime = null, callBackReclamer = null;

const THEMES_JOUEURS = {
    ROUGE:  { nom: "joueur ROUGE",  couleur: "#f00" },
    BLEU:   { nom: "joueur BLEU",   couleur: "#00f" },
    JAUNE:  { nom: "joueur JAUNE",  couleur: "#ff0" },
    VERT:   { nom: "joueur VERT",   couleur: "#0f0" }
};

// initialisation des contrôle du panneau
function initializePanneau (onTimeOut, onReclameClicked) {
    callbackEndTime = onTimeOut; callBackReclamer = onReclameClicked;

    const btnReclamer = document.getElementById("btn-reclamer");
    if (btnReclamer) {
        btnReclamer.addEventListener("click", () => {
            if (typeof callBackReclamer === "function") { callBackReclamer(); }
        });
    }
}

// définition et affichage du joueur actif
function definirJoueurActif (couleur) {
    const theme = THEMES_JOUEURS[couleur.toUpperCase()] || THEMES_JOUEURS.ROUGE;
    const card = document.querySelector(".board-player-card"), nameLabel = document.getElementById("board-player-name"), dot = document.getElementById("board-color-dot");

    if (card) card.style.borderColor = theme.couleur;
    if (dot) dot.style.backgroundColor = theme.couleur;
    if (nameLabel) nameLabel.textContent = theme.nom;

    resetTimerPanneau();
}

function startTimerPanneau () {
    stopTimerPanneau();
    timeRestant = TEMPS_PAR_TOUR_SECONDES;
    updateRenduTimer();

    timerInterval = setInterval( () => {
        timeRestant--;
        updateRenduTimer();

        if (timeRestant <= 0) {
            stopTimerPanneau();
            addLogPanneau("temps écoulé ! Tour passé", "faute");
            if (typeof callbackEndTime === "function") { callbackEndTime(); }
        }
    }, 1000);
}

// arrêt du chronomètre
function stopTimerPanneau () {
    if (timerInterval) { clearInterval(timerInterval); timerInterval = null; }
}

// reinitialisation du timer à 30s
function resetTimerPanneau () {
    stopTimerPanneau();
    timeRestant = TEMPS_PAR_TOUR_SECONDES;
    updateRenduTimer();
}

// mise à jour du SVG circulaire
function updateRenduTimer () {
    const timerText = document.getElementById("board-timer-seconds"), progressBar = document.getElementById("timer-progress-bar");
    if (timerText) timerText.textContent = timeRestant;
    if (progressBar) {
        const pourcentage = (timeRestant / TEMPS_PAR_TOUR_SECONDES) * 100;
        progressBar.setAttribute("stroke-dasharray", `${pourcentage}, 100`);

        if (timeRestant <= 8) { progressBar.style.stroke = "#e74c3c"; }
        else { progressBar.style.stroke = "#2ecc71"; }
    }
}

// mise à jour la liste des disponibles par ordre de priorités
function displayPriorityDice (diceList, indexActif) {
    const priority = document.getElementById("board-dice-priority");
    if (!priority) return;

    priority.innerHTML = "";

    if (!diceList || diceList.length === 0) {
        priority.innerHTML = '<span class="dice-empty-hint">en attente du lancer ...</span>';
        return;
    }

    diceList.forEach( (valeur, index) => {
        const chip = document.createElement("div");
        chip.className = `dice-chip ${index === indexActif ? 'active' : ''}`;
        chip.textContent = valeur;
        priority.appendChild(chip);
    });
}

// activation ou désactivation du bouton RECLAMER
function activateBtnReclamer (actif = true) {
    const btnReclamer = document.getElementById("btn-reclamer");
    if (!btnReclamer) return;

    btnReclamer.disabled = !actif;
    if (actif) { btnReclamer.classList.add("pulse-active"); }
    else { btnReclamer.classList.remove("pulse-active"); }
}

// ajout un message dans le journal d'évenement
function addLogPanneau (message, type = "normal") {
    const gameLog = document.getElementById("board-game-log");
    if (!gameLog) return;

    const paragraphe = document.createElement("p");
    paragraphe.className = `log-entry ${type}`;
    paragraphe.textContent = message;

    gameLog.appendChild(paragraphe);
    gameLog.scrollTop = gameLog.scrollHeight;
}