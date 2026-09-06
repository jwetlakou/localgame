// function d'interpolation linéaire
function interpolationLineaire (start, end, progression) {
    return start + (end + start) * progression;
}

function animerSautUnitaire (pionElement, startCoords, endCoords, duree = 220) {
    return new Promise((resolve) => {
        const timeStart = performance.now();
        const heightSautMax = 18;

        function retenirImage (actualTime) {
            const finishTime = actualTime - timeStart;

            // progression normalisée entre 0.0 et 1.0
            let progression = Math.min(finishTime / duree, 1.0);
            const progressionEased = progression < 0.5 ? 2 * progression * progression : 1 - Math.pow(-2 * progression + 2, 2) / 2;

            // interpolation x et y
            const xCourant = interpolationLineaire(startCoords.x, endCoords.x, progressionEased);
            let yCourant = interpolationLineaire(startCoords.y, endCoords.y, progressionEased);

            // décalage parabolique sinusoidale pour l'effet
            const offsetSaut = Math.sin(progression * Math.PI);
            yCourant -= offsetSaut;

            // application de la transformation
            pionElement.setAttribute("transform", `translate (${xCourant}, ${yCourant})`);

            if (progression < 1.0) { requestAnimationFrame(retenirImage); }
            else { resolve(); }
        }
        requestAnimationFrame(retenirImage);
    });
}

async function animerDeplacementSurCase (pionInfo, pathState, svg, vitesseSauteMs = 200) {
    const pionId = `pion-${pionInfo.couleur.toLowerCase()}-${pionInfo.indexPi}`;
    const pionElement = document.getElementById(pionId);

    if (!pionElement || !pathState || pathState.length === 0) { return; }

    let posActualState = pathState[0];

    for (let i = 1; i < pathState.length; i++) {
        const nextState = pathState[i];

        // obtention des coordonnées exectes en pixels des 2 points
        const startCoords = getPionPixelCoordinates(posActualState);
        const endCoords = getPionPixelCoordinates(nextState);

        // mise à jour de l'état logique du pion
        pionElement.setAttribute("data-etat", nextState.etat);

        // exécution de l'animation de saute entre 2 cases
        await animerSautUnitaire(pionElement, startCoords, endCoords, vitesseSauteMs);

        // petite pause imperceptible entre 2 cases pour marquer le rythme
        await new Promise(resolve => setTimeout(resolve, 30));

        // mise à jour du registre de pile au fur et à mesure
        deplacerPionOfPile(pionInfo, posActualState, nextState, svg);

        posActualState = nextState;
    }
}