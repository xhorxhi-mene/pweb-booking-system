'use strict';

function eliminaCliente(userId, nome, cognome) {
    let conferma = confirm(`Sei sicuro di voler eliminare definitivamente il cliente ${nome} ${cognome}?\nL'azione è irreversibile e cancellerà anche il suo storico.`);
    
    if (conferma) {
        let formData = new FormData();
        formData.append('delete_user_id', userId);

        fetch('admin_clienti.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(data => {
            if(data.trim() === "OK") {
                location.reload(); // Ricarica la pagina aggiornando la tabella
            } else {
                alert("Errore durante l'eliminazione del cliente dal database.");
            }
        })
        .catch(error => {
            console.error("Errore di rete:", error);
            alert("Si è verificato un errore di connessione.");
        });
    }
}

function generaTabellaClienti(datiDaMostrare) {
    const container = document.getElementById("clienti-table-container");
    container.innerHTML = ""; 

    if (datiDaMostrare.length === 0) {
        const noData = document.createElement("div");
        noData.style.cssText = "padding:40px; text-align:center;";
        noData.innerHTML = "<p>Nessun cliente trovato con questi criteri.</p>";
        container.appendChild(noData);
        return;
    }

    const table = document.createElement("table");
    
    const thead = document.createElement("thead");
    thead.innerHTML = `
        <tr>
            <th>Cognome e Nome</th>
            <th>Telefono</th>
            <th>Email</th>
            <th>N. Buche</th>
            <th>Stato</th>
            <th>Azioni</th> <!-- Aggiunta intestazione Azioni -->
        </tr>
    `;
    table.appendChild(thead);

    const tbody = document.createElement("tbody");

    for (let row of datiDaMostrare) {
        let tr = document.createElement("tr");

        let tdNome = document.createElement("td");
        tdNome.style.cssText = "text-align:left; padding-left:20px; word-break: break-all;";
        tdNome.innerHTML = `<strong>${row.cognome}</strong> ${row.nome}`;
        tr.appendChild(tdNome);

        let tdPhone = document.createElement("td");
        tdPhone.innerText = row.phone;
        tr.appendChild(tdPhone);

        let tdEmail = document.createElement("td");
        tdEmail.innerHTML = `<small>${row.email}</small>`;
        tdEmail.style.cssText = "text-align:left; padding-left:20px; word-break: break-all;";
        tr.appendChild(tdEmail);

        let tdBuche = document.createElement("td");
        tdBuche.innerText = row.no_show_count;
        tr.appendChild(tdBuche);

        let tdStato = document.createElement("td");
        // Converto in intero per sicurezza
        if (parseInt(row.is_blacklisted) === 1) {
            tdStato.innerHTML = `<span class="status-badge status-bl">In Blacklist</span>`;
        } else {
            tdStato.innerHTML = `<span class="status-badge status-ok">Attivo</span>`;
        }
        tr.appendChild(tdStato);

        let tdAzioni = document.createElement("td");
        tdAzioni.className = "actions-cell";
        
        let btnElimina = document.createElement("button");
        btnElimina.className = "btn-delete";
        btnElimina.innerText = "Elimina";
        btnElimina.onclick = () => eliminaCliente(row.user_id, row.nome, row.cognome);
        
        tdAzioni.appendChild(btnElimina);
        tr.appendChild(tdAzioni);

        tbody.appendChild(tr);
    }

    table.appendChild(tbody);
    container.appendChild(table);
}

function applicaFiltriClienti() {
    const testoRicerca = document.getElementById("search-bar").value.toLowerCase();
    const opzioneScelta = document.getElementById("service").value;

    let clientiFiltrati = clienti.filter(row => {
        const nomeCompleto = (row.nome + " " + row.cognome).toLowerCase();
        const cognomeNome = (row.cognome + " " + row.nome).toLowerCase();
        const telefono = row.phone.toLowerCase();
        const email = row.email.toLowerCase();

        const matchTesto = testoRicerca === "" || 
                           nomeCompleto.includes(testoRicerca) || 
                           cognomeNome.includes(testoRicerca) || 
                           telefono.includes(testoRicerca) || 
                           email.includes(testoRicerca);

        let matchStato = true; 
        if (opzioneScelta === "blacklist") {
            matchStato = parseInt(row.is_blacklisted) === 1;
        } else if (opzioneScelta === "attivi") {
            matchStato = parseInt(row.is_blacklisted) === 0;
        }

        return matchTesto && matchStato;
    });

    if (opzioneScelta === "recent") {
        clientiFiltrati.sort((a, b) => parseInt(b.user_id) - parseInt(a.user_id));
    } else {
        clientiFiltrati.sort((a, b) => {
            let confrontoCognome = a.cognome.localeCompare(b.cognome);
            if (confrontoCognome !== 0) {
                return confrontoCognome;
            }
            return a.nome.localeCompare(b.nome);
        });
    }

    generaTabellaClienti(clientiFiltrati);
}

function setup() {
    if (document.getElementById("clienti-table-container")) {
        generaTabellaClienti(clienti);
    }

    const searchBar = document.getElementById("search-bar");
    if (searchBar) {
        searchBar.addEventListener('input', applicaFiltriClienti);
    }
    
    const sortFilter = document.getElementById("service");
    if (sortFilter) {
        sortFilter.addEventListener('change', applicaFiltriClienti);
    }
}

document.addEventListener('DOMContentLoaded', setup);