'use strict';

function generaTabellaAdmin(datiDaMostrare) {
    const container = document.getElementById("admin-table-container");
    container.innerHTML = "";

    if (datiDaMostrare.length === 0) {
        const noData = document.createElement("div");
        noData.className = "no-appointments";
        noData.style.cssText = "padding:40px; text-align:center;";
        noData.innerHTML = "<p>Nessuna prenotazione trovata.</p>";
        container.appendChild(noData);
        return;
    }

    const table = document.createElement("table");
    
    const thead = document.createElement("thead");
    thead.innerHTML = `
        <tr>
            <th>Data e Ora</th>
            <th>Cliente / Telefono</th>
            <th>Servizio</th>
            <th>Azioni</th>
        </tr>
    `;
    table.appendChild(thead);

    const tbody = document.createElement("tbody");

    for (let row of datiDaMostrare) {
        let tr = document.createElement("tr");

        let tdData = document.createElement("td");
        tdData.innerHTML = `<strong>${row.date_formatted}</strong><br>${row.time_formatted}`;
        tr.appendChild(tdData);

        let tdCliente = document.createElement("td");
        tdCliente.className = "client-info";
        let nomeCompleto = row.cognome + " " + row.nome;
        tdCliente.innerHTML = `<strong>${nomeCompleto}</strong><br><span>Tel: ${row.phone}</span>`;
        tr.appendChild(tdCliente);

        let tdServizio = document.createElement("td");
        tdServizio.innerText = row.servizio;
        tr.appendChild(tdServizio);

        let tdAzioni = document.createElement("td");
        tdAzioni.className = "actions-cell";
        tdAzioni.innerHTML = `
            <a href="../script/segna_buca.php?id=${row.user_id}" 
               class="btn-buca" 
               onclick="return confirm('Segnare buca a questo cliente?')">
               Buca (${row.no_show_count})
            </a>
            <a href="../script/cancella_appuntamento.php?id=${row.appointment_id}" 
               class="btn-delete" 
               onclick="return confirm('Vuoi davvero eliminare questa prenotazione?')">
               Elimina
            </a>
        `;
        tr.appendChild(tdAzioni);

        tbody.appendChild(tr);
    }

    table.appendChild(tbody);
    container.appendChild(table);
}


function applicaFiltri() {
    const testoRicerca = document.getElementById("search-bar").value.toLowerCase();
    const servizioSelezionato = document.getElementById("service").value;
    
    const periodoSelezionato = document.getElementById("time-filter").value;
    const dataSpecifica = document.getElementById("date-filter").value; 

    const oraAttuale = new Date();
    
    const anno = oraAttuale.getFullYear();
    const mese = String(oraAttuale.getMonth() + 1).padStart(2, '0');
    const giorno = String(oraAttuale.getDate()).padStart(2, '0');
    const oggiString = `${anno}-${mese}-${giorno}`;

    const prenotazioniFiltrate = prenotazioni.filter(row => {
        const nomeCompleto = (row.nome + " " + row.cognome).toLowerCase();
        const cognomeNome = (row.cognome + " " + row.nome).toLowerCase();
        const telefono = row.phone.toLowerCase();
        const servizioDesc = row.servizio.toLowerCase();

        const matchTesto = testoRicerca === "" || 
                           nomeCompleto.includes(testoRicerca) || 
                           cognomeNome.includes(testoRicerca) || 
                           telefono.includes(testoRicerca);

        const matchServizio = servizioSelezionato === "Tutti" || row.servizio === servizioSelezionato;

        let matchTempo = true;
        
        const appDateObj = new Date(row.start_time.replace(' ', 'T')); 
        const appDateString = row.start_time.split(' ')[0]; 

        if (dataSpecifica !== "") {
            matchTempo = (appDateString === dataSpecifica);
        } else {
            if (periodoSelezionato === "oggi") {
                matchTempo = (appDateString === oggiString);
            } else if (periodoSelezionato === "futuri") {
                matchTempo = (appDateObj >= oraAttuale);
            } else if (periodoSelezionato === "passati") {
                matchTempo = (appDateObj < oraAttuale);
            }
        }

        return matchTesto && matchServizio && matchTempo;
    });

    generaTabellaAdmin(prenotazioniFiltrate);
}

function setup() {
    const searchBar = document.getElementById("search-bar");
    if (searchBar) searchBar.addEventListener('input', applicaFiltri);

    const service = document.getElementById("service");
    if (service) service.addEventListener('change', applicaFiltri);

    const timeFilter = document.getElementById("time-filter");
    if (timeFilter) timeFilter.addEventListener('change', applicaFiltri);

    const dateFilter = document.getElementById("date-filter");
    if (dateFilter) {
        dateFilter.addEventListener('input', applicaFiltri);
        
        dateFilter.addEventListener('change', function() {
            if(this.value !== "") {
                document.getElementById("time-filter").value = "tutti";
                applicaFiltri();
            }
        });
    }

    // cosi' mi mostra subito quelli di oggi
    if (document.getElementById("admin-table-container")) {
        applicaFiltri(); 
    }
}

document.addEventListener('DOMContentLoaded', setup);