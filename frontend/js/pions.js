const COULEURS_LUDO = ['ROUGE', 'BLEU', 'JAUNE', 'VERT'];
const NOMBRE_PIONS_PAR_COULEUR = 4;

const COULEURS_THEME = {
    ROUGE:  { principal: "#f00",  sombre: "#922b21",  brillant: "#fadbd8" },
    BLEU:   { principal: "#00f",  sombre: "#1b4f72",  brillant: "#d4e6f1" },
    JAUNE:  { principal: "#ff0",  sombre: "#9a7d0a",  brillant: "#fcf3cf" },
    VERT:   { principal: "#0f0",  sombre: "#196f3d",  brillant: "#d5f5e3" },
}

// injecte les dégradé SVG dans le plateau
function injecterDefSVG (svg) {
    let defs = svg.querySelector("defs");
    if (!defs) {
        defs = document.createElementNS(SVG_NS, "defs");
        svg.insertBefore(defs, svg.firstChild);
    }

    COULEURS_LUDO.forEach( couleur => {
        const theme = COULEURS_THEME[couleur];
        const gradient = document.createElementNS(SVG_NS, "radialGradient");

        gradient.setAttribute("id", `gradient-pion-${couleur.toLowerCase()}`);
        gradient.setAttribute("cx", "35%");
        gradient.setAttribute("cy", "35%");
        gradient.setAttribute("r", "65%");

        gradient.innerHTML = `
            <stop offset="0%" stop-color="${theme.brillant}" />
            <stop offset="40%" stop-color="${theme.principal}" />
            <stop offset="100%" stop-color="${theme.sombre}" />
        `;
        defs.appendChild(gradient);
    });
}

// génération du groupe SVG (g) répresentant un pion
function createElementPionSVG (couleur, indexPi) {
    const pionId = `pion-${couleur.toLowerCase()}-${indexPi}`;
    const pionLudo = document.createElementNS(SVG_NS, "g");

    pionLudo.setAttribute("id", pionId);
    pionLudo.setAttribute("class", `pion pion-${couleur.toLowerCase()}`);
    pionLudo.setAttribute("data-couleur", couleur);
    pionLudo.setAttribute("data-pion-id", indexPi);
    pionLudo.setAttribute("data-etat", "MAISON");

    // ombre portée sous le pion
    const ombre = document.createElementNS(SVG_NS, "ellipse");
    ombre.setAttribute("cx", "0");
    ombre.setAttribute("cy", "6");
    ombre.setAttribute("rx", "11");
    ombre.setAttribute("ry", "5");
    ombre.setAttribute("fill", "rgba(0, 0, 0, .3)");
    pionLudo.appendChild(ombre);
    
    // corp principale du pion (sphère avec dégradé 3D)
    const corps = document.createElementNS(SVG_NS, "circle");
    corps.setAttribute("cx", "0");
    corps.setAttribute("cy", "0");
    corps.setAttribute("r", "12");
    corps.setAttribute("fill", `url(#gradient-pion-${couleur.toLowerCase()})`);
    corps.setAttribute("stroke", "#fff");
    corps.setAttribute("stroke-width", "1.5");
    pionLudo.appendChild(corps);

    const anneau = document.createElementNS(SVG_NS, "circle");
    anneau.setAttribute("cx", "-3");
    anneau.setAttribute("cy", "-3");
    anneau.setAttribute("r", "4");
    anneau.setAttribute("fill", "none");
    anneau.setAttribute("stroke", "rgba(255, 255, 255, .6)");
    anneau.setAttribute("stroke-width", "1");
    pionLudo.appendChild(anneau);

    return pionLudo;
}

// initialise et place tous les pions sur le plateau
function initializeAllPions (svg) {
    injecterDefSVG(svg);

    let pionGlobalGroup = svg.querySelector("#groupe-pions");
    if (!pionGlobalGroup) {
        pionGlobalGroup = document.createElementNS(SVG_NS, "g");
        pionGlobalGroup.setAttribute("id", "groupe-pions");
        svg.appendChild(pionGlobalGroup);
    }

    COULEURS_LUDO.forEach (couleur => {
        for (let i = 0; i < NOMBRE_PIONS_PAR_COULEUR; i++) {
            const pionSVG = createElementPionSVG(couleur, i);
            
            // calcul des coordonnées d'origine dans la maison via MAPPING
            const coordonnees = getPionPixelCoordinates({
                etat: 'MAISON', couleur: couleur, indexPi: i
            });

            // positionnement spatial via la transformation SVG
            pionSVG.setAttribute("transform", `translate(${coordonnees.x}, ${coordonnees.y})`);
            pionGlobalGroup.appendChild(pionSVG);

            const positionInitiale = { etat: 'MAISON', couleur: couleur, indexPi: i }

            // enregistrement initial dans le gestionnaire de pile
            const cleMaison = obtenirCleCase(positionInitiale);
            if (!registreOccupations[cleMaison]) { registreOccupations[cleMaison] = []; }
            
            registreOccupations[cleMaison].push( { couleur, indexPi: i, ...positionInitiale });
            refreshPileDisplay(cleMaison, svg);
        }
    });
}