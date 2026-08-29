const TAILLE_CASE = 40;
const SVG_NS = "http://www.w3.org/2000/svg";

// cartographie des 5 cases de sécurité pour chaque couleur
const VOIES_SECURITE = {
    ROUGE: [
        { indexSe: 1,   row: 1,   col: 7 },
        { indexSe: 2,   row: 2,   col: 7 },
        { indexSe: 3,   row: 3,   col: 7 },
        { indexSe: 4,   row: 4,   col: 7 },
        { indexSe: 5,   row: 5,   col: 7 }
    ],
    BLEU: [
        { indexSe: 1,   row: 7,   col: 1 },
        { indexSe: 2,   row: 7,   col: 2 },
        { indexSe: 3,   row: 7,   col: 3 },
        { indexSe: 4,   row: 7,   col: 4 },
        { indexSe: 5,   row: 7,   col: 5 }
    ],
    JAUNE: [
        { indexSe: 1,   row: 13,  col: 7 },
        { indexSe: 2,   row: 12,  col: 7 },
        { indexSe: 3,   row: 11,  col: 7 },
        { indexSe: 4,   row: 10,  col: 7 },
        { indexSe: 5,   row: 9,   col: 7 }
    ],
    VERT: [
        { indexSe: 1,   row: 7,   col: 13 },
        { indexSe: 2,   row: 7,   col: 12 },
        { indexSe: 3,   row: 7,   col: 11 },
        { indexSe: 4,   row: 7,   col: 10 },
        { indexSe: 5,   row: 7,   col: 9 }
    ]
}

// création des triangles de victoire du CENTRE
function dessinerCentreCiel (svg) {
    const xMin = 6 * TAILLE_CASE, yMin = 6 * TAILLE_CASE,
          xMax = 9 * TAILLE_CASE, yMax = 9 * TAILLE_CASE,
          xCentre = 7.5 * TAILLE_CASE, yCentre = 7.5 * TAILLE_CASE;

    const triangles = [
        { points: `${xMin},${yMin} ${xMax},${yMin} ${xCentre},${yCentre}`,  couleur: "#e74c3c", classe: "ciel-triangle-rouge" },
        { points: `${xMin},${yMin} ${xMin},${yMax} ${xCentre},${yCentre}`,  couleur: "#3498db", classe: "ciel-triangle-bleu" },
        { points: `${xMin},${yMax} ${xMax},${yMax} ${xCentre},${yCentre}`,  couleur: "#f1c40f", classe: "ciel-triangle-jaune" },
        { points: `${xMax},${yMin} ${xMax},${yMax} ${xCentre},${yCentre}`,  couleur: "#2ecc71", classe: "ciel-triangle-vert" },
    ];

    triangles.forEach( (tri) => {
        const polygon = document.createElementNS(SVG_NS, "polygon");
        polygon.setAttribute("points", tri.points);
        polygon.setAttribute("fill", tri.couleur);
        polygon.setAttribute("stroke", "#fff");
        polygon.setAttribute("stroke-width", "1.5");
        polygon.setAttribute("class", tri.classe);
        svg.appendChild(polygon);
    });

    // label CIEL
    const text = document.createElementNS(SVG_NS, "text");
    text.setAttribute("x", xCentre);
    text.setAttribute("y", yCentre + 4);
    text.setAttribute("text-anchor", "middle");
    text.setAttribute("font-size", "11px");
    text.setAttribute("font-weight", "bold");
    text.setAttribute("fill", "#fff");
    text.style.pointerEvents = "none";
    text.textContent = "CIEL";

    svg.appendChild(text);
}

function appliquerMarquageVoiesSecurite (svg) {
    Object.keys(VOIES_SECURITE).forEach (couleur => {
        VOIES_SECURITE[couleur].forEach (caseInfo => {
            const rect = svg.querySelector(`rect[data-row="${caseInfo.row}"][data-col="${caseInfo.col}"]`);

            if (rect) {
                rect.setAttribute("data-voie-securite", couleur);
                rect.setAttribute("data-index-securite", caseInfo.indexSe);
                rect.classList.add(`voie-${couleur.toLowerCase()}`);

                // numérotation discrête S1 à S5
                const text = document.createElementNS(SVG_NS, "text");
                text.setAttribute("x", caseInfo.col * TAILLE_CASE + TAILLE_CASE / 2);
                text.setAttribute("y", caseInfo.row * TAILLE_CASE + TAILLE_CASE / 2 + 4);
                text.setAttribute("text-anchor", "middle");
                text.setAttribute("font-size", "9px");
                text.setAttribute("fill", "#555");
                text.style.pointerEvents = "none";
                text.textContent = `S${caseInfo.indexSe}`;
                svg.appendChild(text);
            }
        });
    });
}