function redessinerPlateauWithState (gameState, svg) {
    if (!gameState || !gameState.pions) { console.error(`OBJET ETAT DU JEU INVALIDE - ${gameState}`); return; }
    console.log("SYNCHRONISATION INSTANTANEE ETAT DU PLATEAU");

    // nettoyage de l'interface et arrêt des animations
    resetEvidancePions ();
    document.body.classList.remove("ui-locked");

    // repositionnement instantanee de tous les pions
    const couleurs = ["ROUGE", "BLEU", "JAUNE", "VERT"];

    couleurs.forEach( (couleur) => {
        const pionPlayerList = gameState.pions[couleur];
        if (!Array.isArray(pionPlayerList)) return;

        pionPlayerList.forEach( (pionState, indexPi) => {
            const pionId = `pion-${couleur.toLowerCase()}-${indexPi}`;
            const pionElement = document.getElementById(pionId);
            if (!pionElement) return;

            const etatComplet = { ...pionState, couleur: couleur, indexPi: indexPi };
            const coordonnées = getPionPixelCoordinates(etatComplet);

            // positionement instantané
            pionElement.setAttribute("transform", `translate(${coordonnées.x}, ${coordonnées.y})`);
            pionElement.setAttribute("data-etat", pionState.etat || "MAISON");
        });
    });

    // calcul global des piles des pions pour les décalages visuels
    if (typeof recalculateAllPions === "function") { recalculateAllPions(svg); }
    
    // synchronisation du tableau
    if (gameState.joueurActif) { definirJoueurActif(gameState.joueurActif); }
    if (Array.isArray(gameState.dices)) { displayPriorityDice(gameState.dices, gameState.indexActif || 0); }

    // journalisation
    addLogPanneau("plateau synchronisé avec état du serveur", "serveur");
}