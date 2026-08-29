const TAILLE_GRILLE = 15;
// const TAILLE_CASE = 40;
// const SVG_NS = "http://www.w3.org/2000/svg";

// coordonnées (row, col) des cases de DEPART [index = 0 pour chaque couleur]
const CASES_DEPART = {
    ROUGE:  { row: 0,   col: 6 },
    BLEU:   { row: 8,  col: 0 },
    JAUNE:  { row: 14,   col: 8 },
    VERT:   { row: 6,   col: 14 }
}

const CONFIG_MAISONS = [
    { couleur: "ROUGE", row: 0, col: 0, classSocle: "socle-rouge" },
    { couleur: "BLEU",  row: 9, col: 0, classSocle: "socle-bleu" },
    { couleur: "JAUNE", row: 9, col: 9, classSocle: "socle-jaune" },
    { couleur: "VERT",  row: 0, col: 9, classSocle: "socle-vert" }
];

document.addEventListener("DOMContentLoaded", () => { creerPlateau(); });

function creerPlateau () {
    const container = document.getElementById('board-container');
    container.innerHTML = "";
    const svg = document.createElementNS(SVG_NS, "svg");

    svg.setAttribute("width", TAILLE_GRILLE * TAILLE_CASE);
    svg.setAttribute("height", TAILLE_GRILLE * TAILLE_CASE);
    svg.setAttribute("viewBox", `0 0 ${TAILLE_GRILLE * TAILLE_CASE} ${TAILLE_GRILLE * TAILLE_CASE}`);

    // construction de la matrice 15X15
    for (let row = 0; row < TAILLE_GRILLE; row++) {
        for (let col = 0; col < TAILLE_GRILLE; col++) {
            const rect = document.createElementNS(SVG_NS, "rect");
            rect.setAttribute("x", col * TAILLE_CASE);
            rect.setAttribute("y", row * TAILLE_CASE);
            rect.setAttribute("width", TAILLE_CASE);
            rect.setAttribute("height", TAILLE_CASE);
            rect.setAttribute("data-row", row);
            rect.setAttribute("data-col", col);

            // classification visuelle des zones
            const classCase = obtenirClasseCase(row, col);
            rect.setAttribute("class", `grid-cell ${classCase}`);

            svg.appendChild(rect);
        }
    }
    appliquerMarquageVoiesSecurite(svg);
    appliquerMarquageCircuit(svg);
    dessinMaisonSocles(svg);
    dessinerCentreCiel(svg);

    container.appendChild(svg);
}

function obtenirClasseCase (row, col) {
    // vérification la case de DEPART[0] de chaque couleur
    if (row === CASES_DEPART.ROUGE.row && col === CASES_DEPART.ROUGE.col) return "depart-rouge";
    if (row === CASES_DEPART.BLEU.row && col === CASES_DEPART.BLEU.col) return "depart-bleu";
    if (row === CASES_DEPART.JAUNE.row && col === CASES_DEPART.JAUNE.col) return "depart-jaune";
    if (row === CASES_DEPART.VERT.row && col === CASES_DEPART.VERT.col) return "depart-vert";

    // vérification de la MAISON[6X6] de chaque couleur
    if (row < 6 && col < 6) return "maison-rouge";
    if (row > 8 && col < 6) return "maison-bleu";
    if (row > 8 && col > 8) return "maison-jaune";
    if (row < 6 && col > 8) return "maison-vert";

    // vérification des CASES du CENTRE de chaque couleur
    if (row === 6 && col === 7) return "centre-arrivee-rouge";
    if (row === 7 && col === 6) return "centre-arrivee-bleu";
    if (row === 8 && col === 7) return "centre-arrivee-jaune";
    if (row === 7 && col === 8) return "centre-arrivee-vert";

    // vérification du zone centrale[3x3]
    if (row >= 6 && row <= 8 && col >= 6 && col <= 8) return "centre";

    // vérification de VOIES SECURITE vers le CENTRE
    if (col === 7 && row >= 1 && row <= 5) return "voie-securite-rouge";
    if (row === 7 && col >= 1 && col <= 5) return "voie-securite-bleu";
    if (col === 7 && row >= 9 && row <= 13) return "voie-securite-jaune";
    if (row === 7 && col >= 9 && col <= 13) return "voie-securite-vert";

    return "";
}

function dessinMaisonSocles (svg) {
    CONFIG_MAISONS.forEach( (maison) => {
        const xOrigine = maison.col * TAILLE_CASE;
        const yOrigine = maison.row * TAILLE_CASE;

        // cadre blanc intérieur (4X4 CASES centré au milieu)
        const cadre = document.createElementNS(SVG_NS, "rect");
        cadre.setAttribute("x", xOrigine + TAILLE_CASE);
        cadre.setAttribute("y", yOrigine + TAILLE_CASE);
        cadre.setAttribute("width", TAILLE_CASE * 4);
        cadre.setAttribute("height", TAILLE_CASE * 4);
        cadre.setAttribute("class", "cadre-maison");

        // positions relatives 4 SOCLES du CADRE (2x2)
        const offsetSocles = [
            {dx: TAILLE_CASE * 2,   dy: TAILLE_CASE * 2 },
            {dx: TAILLE_CASE * 2,   dy: TAILLE_CASE * 4 },
            {dx: TAILLE_CASE * 4,   dy: TAILLE_CASE * 4 },
            {dx: TAILLE_CASE * 4,   dy: TAILLE_CASE * 2 }
        ];

        // création des 4 SOCLES DES PIONS
        offsetSocles.forEach( (offset, index) => {
            const circle = document.createElementNS(SVG_NS, "circle");
            circle.setAttribute("cx", xOrigine + offset.dx);
            circle.setAttribute("cy", yOrigine + offset.dy);
            circle.setAttribute("r", 14);
            circle.setAttribute("class", `socle-pion ${maison.classSocle}`);
            circle.setAttribute("data-couleur", maison.couleur);
            circle.setAttribute("data-index-socle", index);

            svg.appendChild(circle);
        }); 
    });
}

function appliquerMarquageCircuit (svg) {
    CIRCUIT.forEach( (caseInfo) => {
        // selection de la case correspondante
        const rect = svg.querySelector(`rect[data-row="${caseInfo.row}"][data-col="${caseInfo.col}"]`);
        if (!rect) return;

        rect.setAttribute("data-index-circuit", caseInfo.index);
        rect.setAttribute("data-type-case", caseInfo.type);

        // application des styles visuels
        if (caseInfo.type.startsWith("DEPART_")) {
            rect.classList.add(`depart-${caseInfo.couleur.toLowerCase()}`);
        } else {
            rect.classList.add("case-circuit-neutre");
        }

        // ajout du numéro d'index au centre de la case
        const text = document.createElementNS(SVG_NS, "text");
        text.setAttribute("x", caseInfo.col * TAILLE_CASE + TAILLE_CASE / 2);
        text.setAttribute("y", caseInfo.row * TAILLE_CASE + TAILLE_CASE / 2 + 4);
        text.setAttribute("text-anchor", "middle");
        text.setAttribute("font-size", "10px");
        text.setAttribute("fill", caseInfo.type.startsWith("DEPART_") ? "#fff" : "#7f8c8d");
        text.style.pointerEvents = "none";
        text.textContent = caseInfo.index;

        svg.appendChild(text);

        // EFFET SE SURVOL
        rect.addEventListener("mouseenter", () => {
            rect.classList.add("case-hover");
            text.textContent = caseInfo.indexRelatif;
            text.setAttribute("font-weight", "bold");
            text.setAttribute("font-size", "13px");
            text.setAttribute("fill", caseInfo.type.startsWith("DEPART_") ? "#fff" : "#e67e22");
        });

        rect.addEventListener("mouseleave", () => {
            rect.classList.add("case-hover");
            text.textContent = caseInfo.index;
            text.setAttribute("font-weight", "normal");
            text.setAttribute("font-size", "10px");
            text.setAttribute("fill", caseInfo.type.startsWith("DEPART_") ? "#fff" : "#7f8c8d");
        });
    });
}