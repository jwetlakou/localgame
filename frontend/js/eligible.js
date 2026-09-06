// active la surbrillance sur une liste de pions éligibles et écoute le clic du joueur
function activatePionsEligibles (pionsEligibleList, callbackOnClick) {
    resetPionsEligibles();

    const allPions = document.querySelectorAll(".pion");
    
    // assombrissement léger des pions non jouables
    allPions.forEach( pion => {
        pion.classList.add("pion-inactif");
    });

    pionsEligibleList.forEach( pionInfo => {
        const pionId = `pion-${pionInfo.couleur.toLowerCase()}-${pionInfo.indexPi}`;
        const pionElement = document.getElementById(pionId);

        if (pionElement) {
            pionElement.classList.remove("pion-inactif");
            pionElement.classList.add("pion-eligible");

            // gestionnaire de clic unique
            const clicManagement = (e) => {
                e.stopPropagation();
                resetPionsEligibles();

                if (typeof callbackOnClick === "function") { callbackOnClick(pionInfo); }
            };

            pionElement._clicManagement = clicManagement;
            pionElement.addEventListener("click", clicManagement, { once: true });
        }
    });
}

// retire toutes les animations de surbrillance et restaure l'état normal des pions
function resetPionsEligibles () {
    const allPions = document.querySelectorAll(".pion");

    allPions.forEach( pion => {
        pion.classList.remove("pion-eligible", "pion-inactif");

        // retrait du gestionnaire de clic
        if (pion._clicManagement) {
            pion.removeEventListener("click", pion._clicManagement);
            pion._clicManagement = null;
        }
    });
}

// calcul d'éligibilité selon la valeur du dé
function calculateEligiblePions (couleurJoueur, valeursDes) {
    const pionEligibles = [];
    const de1 = valeursDes[0];
    const de2 = valeursDes[1];
    const containsSix = (de1 === 6 || de2 === 6)

    // parcours des pions de la couleur actif
    for (let i = 0; i < 4; i++) {
        const pionId = `pion-${couleurJoueur.toLowerCase()}-${i}`;
        const pionElement = document.getElementById(pionId);
        if (!pionElement) continue;

        const etatActuel = pionElement.getAttribute("data-etat");

        // règle 1 - un pion à la maison peut sortir uniquement sur 6
        if (etatActuel === 'MAISON' && containsSix) { pionEligibles.push({ couleur: couleurJoueur, indexPi: i }); }
        
        // règle 1 - un pion à la maison peut sortir uniquement sur 6
        else if (etatActuel === 'CIRCUIT' || etatActuel === 'SECURITE') { pionEligibles.push({ couleur: couleurJoueur, indexPi: i }); }
    }
    return pionEligibles;
}

// activation de la surbrillance et l'interactivite sur la liste des pions
function miseEnEvidancePionEligible (pionsEligibleList) {
    resetEvidancePions ();

    if (!Array.isArray(pionsEligibleList) || pionsEligibleList.length === 0) { return; }
    pionsEligibleList.forEach( (pion) => {
        const pionId = `pion-${pion.couleur.toLowerCase()}-${pion.indexPi}`;
        const pionElement = document.getElementById(pionId);
        if (pionElement) { pionElement.classList.add("pion-eligible"); }
    });
}

// retire la surbrillance de tous les pions du plateau
function resetEvidancePions () {
    const allPions = document.querySelectorAll(".pion");
    allPions.forEach( (pion) => {
        pion.classList.remove("pion-eligible");
    });
}