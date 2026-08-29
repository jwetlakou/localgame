function appliquerEtatMiroir (etatMiroir) {
    if (!etatMiroir || !etatMiroir.joueurActuel) return;

    // mise à jour des infos de session
    document.getElementById('joueur-actuel').textContent = etatMiroir.joueurActuel.nom;
    document.getElementById('joueur-couleur').textContent = etatMiroir.joueurActuel.couleur;
    document.getElementById('statut-partie').textContent = etatMiroir.joueurActuel.statut;

    const [de1, de2] = etatMiroir.des.valeurs;
    document.getElementById('resultat-des').textContent = `dé 1: ${de1} | dé 2: ${de2}`;

    // rendu miroir des pions
    etatMiroir.joueurs.forEach(joueur => {
        joueur.pions.forEach(pion => {
            updateVisuelPion(joueur.couleur, pion.id, pion.etat, pion.position);
        });
    });
}

function updateVisuelPion (couleur, pionId, etat, position) {
    const elPion = document.querySelector(`.pion[data-couleur="${couleur}"][data-id="${pionId}"]`);
    if (!elPion) return;

    elPion.dataset.etat = etat;
    elPion.dataset.position = position;
}

function synchroniserControle (etatPartie) {
    const btnLancer = document.getElementById('btn-lancer-des'),
          formeMouvement = document.getElementById('forme-mouvement'),
          badgeEtat = document.getElementById('statut-partie');

    if (badgeEtat) badgeEtat.textContent = etatPartie;

    switch (etatPartie) {
        case 'ATTENTE_LANCER':
            btnLancer.disabled = false;
            formeMouvement.querySelectorAll('input, button').forEach(el => el.disabled = true);
            break;
        
        case 'CHOIX_PION':
            btnLancer.disabled = true;
            formeMouvement.querySelectorAll('input, button').forEach(el => el.disabled = false);
            break;

        case 'ANIMATION_LOADING':
        case 'FAUTE_RECLAMEE':
        case 'PARTIE_TERMINEE':
            btnLancer.disabled = true;
            formeMouvement.querySelectorAll('input, button').forEach(el => el.disabled = true);
            break;
    }

    formeMouvement.addEventListener('submit', async (e) => {
        e.preventDefault();
        const pionId = parseInt(document.getElementById('pion-id').value, 10);
        const valeurDe = parseInt(document.getElementById('valeur-de').value, 10);

        try {
            const response = await fetch(`${API_URL}?action=deplacer-pion`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({pion_id: pionId, valeur_de: valeurDe})
            });

            const data = await response.json();

            if (!data.succes && !data.coupNul) {
                addLog(`mouvement réfusé : par le serveur : ${data.message}`);
                // animation visuel
            }

            if (data.etat) { appliquerEtatMiroir(data.etat); }
        } catch (error) {
            addLog(`erreur reseau : ${error.message}`);
        }
    });
}

async function reclamerFaute (pionId) {
    try {
        const response = await fetch(`${API_URL}?action=reclamer-faute`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ pion_id: pionId })
        });

        const data = await response.json();

        if (data.succes) {
            addLog(`MANJE : ${data.message}`);
        } else {
            addLog(`reclamation rejetée : ${data.message}`);
        }

        if (data.etat) { appliquerEtatMiroir(data.etat); }
    } catch (error) {
        addLog(`erreur de reclamation : ${error.message}`);
    }
}