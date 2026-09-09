'use strict';

function generaTabellaBlacklist(datiDaMostrare) {
    const container = document.getElementById("blacklist-table-container");
    container.innerHTML = "";

    if (datiDaMostrare.length === 0) {
        const noData = document.createElement("div");
        noData.style.cssText = "padding:40px; text-align:center; color:#999;";
        noData.innerHTML = "<p>Nessun utente in blacklist al momento o trovato dalla ricerca.</p>";
        container.appendChild(noData);
        return;
    }

    const table = document.createElement("table");
    
    const thead = document.createElement("thead");
    thead.innerHTML = `
        <tr>
            <th>Nome e Cognome</th>
            <th>Email</th>
            <th>Telefono</th>
            <th>Buche</th>
            <th>Azioni</th>
        </tr>
    `;
    table.appendChild(thead);

    const tbody = document.createElement("tbody");

    for (let row of datiDaMostrare) {
        let tr = document.createElement("tr");

        let tdNome = document.createElement("td");
        tdNome.innerHTML = `<strong>${row.nome}</strong> ${row.cognome}`;
        tdNome.style = "word-break: break-all;";
        tr.appendChild(tdNome);

        let tdEmail = document.createElement("td");
        tdEmail.innerText = row.email;
        tdEmail.style = "word-break: break-all;";
        tr.appendChild(tdEmail);

        let tdPhone = document.createElement("td");
        tdPhone.innerText = row.phone;
        tr.appendChild(tdPhone);

        let tdBuche = document.createElement("td");
        tdBuche.innerText = row.no_show_count;
        tr.appendChild(tdBuche);

        let tdAzioni = document.createElement("td");
        // Utilizziamo la nuova classe CSS .btn-grazia creata nel file PHP
        tdAzioni.innerHTML = `
            <a href="../script/rimuovi_blacklist.php?id=${row.user_id}"
               class="btn-grazia"
               onclick="return confirm('Vuoi graziare questo utente e azzerare il suo contatore buche?')">
               Grazia
            </a>
        `;
        tr.appendChild(tdAzioni);

        tbody.appendChild(tr);
    }

    table.appendChild(tbody);
    container.appendChild(table);
}

function filtraBlacklist() {
    const input = document.getElementById("search-bar").value.toLowerCase();

    const blacklistFiltrata = blacklistedUsers.filter(row => {
        const nomeCompleto = (row.nome + " " + row.cognome).toLowerCase();
        const cognomeNome = (row.cognome + " " + row.nome).toLowerCase();
        const telefono = row.phone.toLowerCase();
        const email = row.email.toLowerCase();

        return nomeCompleto.includes(input) || 
               cognomeNome.includes(input) || 
               telefono.includes(input) || 
               email.includes(input);
    });

    generaTabellaBlacklist(blacklistFiltrata);
}

function setup() {
    if (document.getElementById("blacklist-table-container")) {
        generaTabellaBlacklist(blacklistedUsers);
    }

    const searchBar = document.getElementById("search-bar");
    if (searchBar) {
        searchBar.addEventListener('input', filtraBlacklist);
    }
}

document.addEventListener('DOMContentLoaded', setup);