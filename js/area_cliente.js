'use strict';

function renderDashboard() {
    const navContainer = document.getElementById("dashboard-nav");
    
    if (!appFuturo) {
        const btnLink = document.createElement("a");
        btnLink.href = "prenota.php";
        btnLink.innerHTML = `<button class="btn-prenota new">Nuova prenotazione</button>`;
        navContainer.appendChild(btnLink);
    }

    const futuroContainer = document.getElementById("futuro-container");
    
    if (appFuturo) {
        let cardHTML = `
            <div class="dashboard-card info-card">
                <p class="dashboard-card-subtitle">Prenotazione Confermata</p>
                <h2 class="dashboard-card-date">${appFuturo.data_formattata}</h2>
                <h3 class="dashboard-card-time">alle ore ${appFuturo.ora_formattata}</h3>
                <hr style="margin: 15px 0;">
                <p class="dashboard-card-details">
                    <strong>Servizio:</strong> ${appFuturo.servizio}<br>
                    <span>Durata stimata: ${appFuturo.duration_minutes} min</span>
                </p>
                <div>
        `;

        if (appFuturo.puo_cancellare) {
            cardHTML += `
                <a href="../script/cancella_appuntamento.php?id=${appFuturo.appointment_id}"
                   class="btn-buca" style="background:#e74c3c; padding: 10px 20px; font-size: 1em;"
                   onclick="return confirm('Vuoi davvero cancellare questo appuntamento?')">Annulla Appuntamento</a>
            `;
        } else {
            cardHTML += `
                <div class="alert warning" style="margin-bottom: 0; padding: 10px;">
                    Impossibile annullare (mancano meno di 2 ore)
                </div>
            `;
        }

        cardHTML += `</div></div>`;
        futuroContainer.innerHTML = cardHTML;
        
    } else {
        futuroContainer.innerHTML = `
            <div class="dashboard-empty">
                Non hai nessun appuntamento in programma.<br>
                Clicca su "Nuova prenotazione" per scegliere un orario!
            </div>
        `;
    }

    const storicoContainer = document.getElementById("storico-container");
    
    if (storicoAppuntamenti.length === 0) {
        storicoContainer.innerHTML = `
            <div style="text-align:center; color:#999; padding:40px;">
                Nessun appuntamento passato.
            </div>
        `;
    } else {
        const table = document.createElement("table");
        table.innerHTML = `
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Ora</th>
                    <th>Servizio</th>
                    <th>Stato</th>
                </tr>
            </thead>
        `;
        
        const tbody = document.createElement("tbody");
        
        storicoAppuntamenti.forEach(app => {
            let tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${app.data_formattata}</td>
                <td>${app.ora_formattata}</td>
                <td>${app.servizio}</td>
                <td><span class="status-badge status-done"></span></td>
            `;
            tbody.appendChild(tr);
        });
        
        table.appendChild(tbody);
        storicoContainer.appendChild(table);
    }
}

document.addEventListener('DOMContentLoaded', renderDashboard);