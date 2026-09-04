// coordonnées des 4 positions de pions
const POSITIONS_MAISON = {
    ROUGE: [
        { row: 1.5,  col: 1.5 }, { row: 1.5,  col: 3.5 },
        { row: 3.5,  col: 1.5 }, { row: 3.5,  col: 3.5 }
    ],

    BLEU: [
        { row: 10.5,  col: 1.5 }, { row: 10.5,  col: 3.5 },
        { row: 12.5,  col: 1.5 }, { row: 12.5,  col: 3.5 }
    ],

    JAUNE: [
        { row: 10.5,  col: 10.5 }, { row: 10.5,  col: 12.5 },
        { row: 12.5,  col: 10.5 }, { row: 12.5,  col: 12.5 }
    ],

    VERT: [
        { row: 1.5,  col: 10.5 }, { row: 1.5,  col: 12.5 },
        { row: 3.5,  col: 10.5 }, { row: 3.5,  col: 12.5 }
    ],
}

// conversion de la paire (col, row) en coordonnées pixels du centre
function gridToPixelCoords (row, col) {
    return { x: col * TAILLE_CASE + (TAILLE_CASE / 2), y: row * TAILLE_CASE + (TAILLE_CASE / 2) };
}

// obtention du centre (x, y) d'une case du circuit principal (0 à 51)
function getCircuitPixelCoords (index) {
    const caseData = CIRCUIT[((index % 52) + 52) % 52];
    if (!caseData) return null;

    return gridToPixelCoords(caseData.row, caseData.col);
}

// obtention du centre (x, y) d'une case de la voie de sécurité (1 à 5)
function getVoieSecuritePixelCoords (couleur, indexSe) {
    const voie = VOIES_SECURITE[couleur.toUpperCase()];
    if (!voie) return null;

    const caseData = voie.find(cd => cd.indexSe === indexSe);
    return caseData ? gridToPixelCoords(caseData.row, caseData.col) : null;
}

// obtention de la position (x, y) d'un pion encore à la maison
function getMaisonPixelCoords (couleur, indexPi) {
    const positionList = POSITIONS_MAISON[couleur.toUpperCase()];
    if (!positionList || !positionList[indexPi]) return null;

    const position = positionList[indexPi];
    return gridToPixelCoords(position.row, position.col);
}

// obtion de la position (x, y) d'arrivée au CENTRE
function getCielPixelCoords (couleur) {
    // les pions se convergent vers le centre du plateau
    const xCentre = (TAILLE_GRILLE * TAILLE_CASE) / 2;
    const yCentre = (TAILLE_GRILLE * TAILLE_CASE) / 2;

    const offsets = {
        ROUGE:   { x: 0,   y: -20 },
        BLEU:  { x: -20, y: 0 },
        JAUNE:   { x: 0,   y: 20 },
        VERT:  { x: 20,  y: 0 },
    };

    const offset = offsets[couleur.toUpperCase()] || { x: 0, y: 0};
    return { x: xCentre + offset.x, y: yCentre + offset.y };
}

// mapping universel : resolution des coordonnées de n'importe quel etat de pion
function getPionPixelCoordinates (pionState) {
    switch (pionState.etat) {
        case 'MAISON':
            return getMaisonPixelCoords(pionState.couleur, pionState.indexPi);
            break;
        case 'CIRCUIT':
            return getCircuitPixelCoords(pionState.index);
            break;
        case 'SECURITE':
            return getVoieSecuritePixelCoords(pionState.couleur, pionState.indexSe);
            break;
        case 'CENTRE':
            return getCielPixelCoords(pionState.couleur);
            break;
        default:
            console.error(`état de pion INCONNU - ${pionState.etat}`);
            return {x: 0, y: 0 };
    }
}

// conversion d'une coordonnées repère SVG local en coordonnées réelles sur l'écran
function svgToScreenCoords (svgElement, svx, svy) {
    const point = svgElement.createSVGPoint();
    point.x = svx;
    point.y = svy;

    const ctm = svgElement.getScreenCTM();
    if (!ctm) return { x: 0, y: 0 };
    const screenPoint = point.matrixTransform(ctm);

    return { clientX: screenPoint.x, clientY: screenPoint.y };
}
