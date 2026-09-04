const DECALAGE_STACK_X = -3;
const DECALAGE_STACK_Y = -4;

// registre des cases occupées
const registreOccupations = {};

// génération d'une clé unique pour identifier une case du plateau
function obtenirCleCase (position) {
    switch (position.etat) {
        case 'CIRCUIT':
            return `CIRCUIT_${position.index}`;
        case 'SECURITE':
            return `SECURITE_${position.couleur}_${position.indexSe}`;
        case 'MAISON':
            return `MAISON_${position.couleur}_${position.indexPi}`;
        case 'CENTRE':
            return `CENTRE_${position.couleur}`;
            break;
        default:
            return 'INCONNU';
    }
}

// application du décalage 3D sur tous les pions d'une case
function refreshPileDisplay (cleCase, svg) {
    const pile = registreOccupations[cleCase];
    if (!pile || pile.length === 0) return;

    const pionContainer = svg.querySelector("#groupe-pions");

    pile.forEach( (pionState, indexOfPile) => {
        const pionId = `pion-${pionState.couleur.toLowerCase()}-${pionState.indexPi}`;
        const pionElement = document.getElementById(pionId);
        if (!pionElement) return;

        // obtention des coordonnées de bases (centre de la case)
        const baseCoordonnees = getPionPixelCoordinates(pionState);

        // calcul du décalage 3D selon le rang dans la pile (0 = base, 1 = above)
        const offsetX = indexOfPile * DECALAGE_STACK_X;
        const offsetY = indexOfPile * DECALAGE_STACK_Y;
        
        const xFinal = baseCoordonnees.x + offsetX;
        const yFinal = baseCoordonnees.y + offsetY;

        // application du positionnement
        pionElement.setAttribute("transform", `translate(${xFinal}, ${yFinal})`);

        // application du LIFO dans le DOM SVG : placer l'élément à la fin du groupe
        if (pionContainer) { pionContainer.appendChild(pionElement); }
    });
}

// deplacement d'un pion d'une case pour empiler
function deplacerPionOfPile (pion, oldPosition, newPosition, svg) {
    const oldCle = obtenirCleCase(oldPosition);
    const newCle = obtenirCleCase(newPosition);

    // retirer le pion dans l'ancienne case
    if (registreOccupations[oldCle]) {
        registreOccupations[oldCle] = registreOccupations[oldCle].filter( p => !(p.couleur === pion.couleur && p.indexPi === pion.indexPi) );
        refreshPileDisplay(oldCle, svg);
    }

    // ajouter le pion au sommet de la nouvelle pile
    if (!registreOccupations[newCle]) { registreOccupations[newCle] = []; }

    const objetPionComplet = { ...pion, ...newPosition };
    registreOccupations[newCle].push(objetPionComplet);

    // mise à jour du rendu de la nouvelle case
    refreshPileDisplay(newCle, svg);
}
