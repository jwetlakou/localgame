const CIRCUIT = [
    // QUADRANT 1 : ROUGE VERS BLEU [0 à 12]
    { index: 0,    row: 0,    col: 6,   indexRelatif: 0,   type: 'DEPART_ROUGE',  couleur: 'ROUGE' },
    { index: 1,    row: 1,    col: 6,   indexRelatif: 1,   type: 'NEUTRE' },
    { index: 2,    row: 2,    col: 6,   indexRelatif: 2,   type: 'NEUTRE' },
    { index: 3,    row: 3,    col: 6,   indexRelatif: 3,   type: 'NEUTRE' },
    { index: 4,    row: 4,    col: 6,   indexRelatif: 4,   type: 'NEUTRE' },
    { index: 5,    row: 5,    col: 6,   indexRelatif: 5,   type: 'NEUTRE' },
    { index: 6,    row: 6,    col: 5,   indexRelatif: 6,   type: 'NEUTRE' },
    { index: 7,    row: 6,    col: 4,   indexRelatif: 7,   type: 'NEUTRE' },
    { index: 8,    row: 6,    col: 3,   indexRelatif: 8,   type: 'NEUTRE' },
    { index: 9,    row: 6,    col: 2,   indexRelatif: 9,   type: 'NEUTRE' },
    { index: 10,   row: 6,    col: 1,   indexRelatif: 10,  type: 'NEUTRE' },
    { index: 11,   row: 6,    col: 0,   indexRelatif: 11,  type: 'NEUTRE' },
    { index: 12,   row: 7,    col: 0,   indexRelatif: 12,  type: 'NEUTRE' },

    // QUADRANT 2 : BLEU VERS JAUNE [13 à 25]
    { index: 13,   row: 8,    col: 0,   indexRelatif: 0,   type: 'DEPART_BLEU',  couleur: 'BLEU' },
    { index: 14,   row: 8,    col: 1,   indexRelatif: 1,   type: 'NEUTRE' }, // 1
    { index: 15,   row: 8,    col: 2,   indexRelatif: 2,   type: 'NEUTRE' }, // 2
    { index: 16,   row: 8,    col: 3,   indexRelatif: 3,   type: 'NEUTRE' }, // 3
    { index: 17,   row: 8,    col: 4,   indexRelatif: 4,   type: 'NEUTRE' }, // 4
    { index: 18,   row: 8,    col: 5,   indexRelatif: 5,   type: 'NEUTRE' }, // 5
    { index: 19,   row: 9,    col: 6,   indexRelatif: 6,   type: 'NEUTRE' }, // 6
    { index: 20,   row: 10,   col: 6,   indexRelatif: 7,   type: 'NEUTRE' }, // 7
    { index: 21,   row: 11,   col: 6,   indexRelatif: 8,   type: 'NEUTRE' }, // 8
    { index: 22,   row: 12,   col: 6,   indexRelatif: 9,   type: 'NEUTRE' }, // 9
    { index: 23,   row: 13,   col: 6,   indexRelatif: 10,  type: 'NEUTRE' }, // 10
    { index: 24,   row: 14,   col: 6,   indexRelatif: 11,  type: 'NEUTRE' }, // 11
    { index: 25,   row: 14,   col: 7,   indexRelatif: 12,  type: 'NEUTRE' }, // 12

    // QUADRANT 3 : JAUNE VERS VERT [13 à 25]
    { index: 26,   row: 14,   col: 8,   type: 'DEPART_JAUNE',  couleur: 'JAUNE' },
    { index: 27,   row: 13,   col: 8,   type: 'NEUTRE' }, // 1
    { index: 28,   row: 12,   col: 8,   type: 'NEUTRE' }, // 2
    { index: 29,   row: 11,   col: 8,   type: 'NEUTRE' }, // 3
    { index: 30,   row: 10,   col: 8,   type: 'NEUTRE' }, // 4
    { index: 31,   row: 9,    col: 8,   type: 'NEUTRE' }, // 5
    { index: 32,   row: 8,    col: 9,   type: 'NEUTRE' }, // 6
    { index: 33,   row: 8,    col: 10,  type: 'NEUTRE' }, // 7
    { index: 34,   row: 8,    col: 11,  type: 'NEUTRE' }, // 8
    { index: 35,   row: 8,    col: 12,  type: 'NEUTRE' }, // 9
    { index: 36,   row: 8,    col: 13,  type: 'NEUTRE' }, // 10
    { index: 37,   row: 8,    col: 14,  type: 'NEUTRE' }, // 11
    { index: 38,   row: 7,    col: 14,  type: 'NEUTRE' }, // 12

    // QUADRANT 4 : JAUNE VERS VERT [39 à 51]
    { index: 39,   row: 6,    col: 14,  type: 'DEPART_VERT',  couleur: 'VERT' },
    { index: 40,   row: 6,    col: 13,  type: 'NEUTRE' }, // 1
    { index: 41,   row: 6,    col: 12,  type: 'NEUTRE' }, // 2
    { index: 42,   row: 6,    col: 11,  type: 'NEUTRE' }, // 3
    { index: 43,   row: 6,    col: 10,  type: 'NEUTRE' }, // 4
    { index: 44,   row: 6,    col: 9,   type: 'NEUTRE' }, // 5
    { index: 45,   row: 5,    col: 8,   type: 'NEUTRE' }, // 6
    { index: 46,   row: 4,    col: 8,   type: 'NEUTRE' }, // 7
    { index: 47,   row: 3,    col: 8,   type: 'NEUTRE' }, // 8
    { index: 48,   row: 2,    col: 8,   type: 'NEUTRE' }, // 9
    { index: 49,   row: 1,    col: 8,   type: 'NEUTRE' }, // 10
    { index: 50,   row: 0,    col: 8,   type: 'NEUTRE' }, // 11
    { index: 51,   row: 0,    col: 7,   type: 'NEUTRE' }, // 12
];

// recherche infos
function obtenirCaseParCoordonnee (row, col) { return CIRCUIT.find(cir => cir.row === row && cir.col === col) || null; }

function obtenirCoordonneeParIndex (index) {
    const indexNormalise = ((index % 52) + 52) % 52;
    return CIRCUIT[indexNormalise];
}