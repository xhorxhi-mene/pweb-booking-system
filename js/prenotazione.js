'use strict';

const GIORNI = ['L', 'M', 'M', 'G', 'V', 'S', 'D'];

function selezionaGiorno(e) {
    let dataScelta = e.target.id;
    // Ricarica la pagina passando la data scelta
    window.location.href = "prenota.php?service_id=" + serviceId + "&m=" + mese + "&y=" + anno + "&data_scelta=" + dataScelta;
}

function generaTabella() {
    const div = document.getElementById("calendario");
    if (!div) return;
    div.innerHTML = "";
    
    let tab = document.createElement("table");
    tab.style.width = "100%";
    
    let trH = document.createElement("tr");
    for (let g of GIORNI) {
        let th = document.createElement("th");
        th.innerText = g;
        th.classList.add("weekday-header");
        trH.appendChild(th);
    }
    tab.appendChild(trH);

    let tr = document.createElement("tr");
    let celleCounter = 0;

    // Creazione celle vuote all'inizio
    for (let i = 1; i < posPrimoGiorno; i++) {
        let td = document.createElement("td");
        td.classList.add("empty", "day-box");
        tr.appendChild(td);
        celleCounter++;
    }

    // Creazione giorni effettivi
    for (let g = 1; g <= giorniNelMese; g++) {
        if (celleCounter > 0 && celleCounter % 7 === 0) {
            tab.appendChild(tr);
            tr = document.createElement("tr");
        }

        let td = document.createElement("td");
        td.innerText = g;
        td.classList.add("day-box");

        let stringaData = anno + "-" + String(mese).padStart(2, '0') + "-" + String(g).padStart(2, '0');

        // Controllo se e' passato, oppure e' Lunedi' (resto 0) o Domenica (resto 6)
        if (stringaData < dataOggi || celleCounter % 7 === 0 || celleCounter % 7 === 6) {
            td.classList.add("expired");
        } else {
            td.id = stringaData;
            td.style.cursor = "pointer";
            td.addEventListener("click", selezionaGiorno);
        }

        tr.appendChild(td);
        celleCounter++;
    }

    if (tr.children.length > 0) {
        while (celleCounter % 7 !== 0) {
            let td = document.createElement("td");
            td.classList.add("empty", "day-box");
            tr.appendChild(td);
            celleCounter++;
        }
        tab.appendChild(tr);
    }

    div.appendChild(tab);
}

function generaOrari() {
    const divOrari = document.getElementById("orari-container");
    if (!divOrari) return;

    divOrari.innerHTML = "";

    // array lineare con tutti gli slot (dalle 09:00 alle 18:30)
    const tuttiGliSlot = [];
    for (let h = 9; h < 19; h++) {
        tuttiGliSlot.push(String(h).padStart(2, '0') + ":00");
        tuttiGliSlot.push(String(h).padStart(2, '0') + ":30");
    }

    // Calcolo di quanti blocchi servono per l'appuntamento corrente
    const slotNecessari = Math.ceil(durataServizioAttuale / 30);

    for (let i = 0; i < tuttiGliSlot.length; i++) {
        let oraSlot = tuttiGliSlot[i];

        // Controllo se l'ora e' passata (oggi)
        if (dataSelezionata === dataOggi && oraSlot <= oraAttuale) {
            if(oraSlot === '18:30'){    
                document.getElementById("msg").innerHTML="Giornata finita";
                return;
            }
            continue; 
        }

        // Verifica restrizioni blacklist: True se l'utente e' bloccato e l'orario NON e' tra i permessi
        let isRestricted = (isBlacklisted && !permessiBl.includes(oraSlot));

        // Controllo Spazio: verifica che questo slot E i successivi siano liberi
        let puoPrenotare = true;
        if (isRestricted) {
            puoPrenotare = false;
        } else {
            for (let j = 0; j < slotNecessari; j++) {
                let indiceControllo = i + j;
                // Se esce dall'orario di chiusura o sbatte su un orario occupato
                if (indiceControllo >= tuttiGliSlot.length || orariOccupati.includes(tuttiGliSlot[indiceControllo])) {
                    puoPrenotare = false;
                    break;
                }
            }
        }

        // Creazione dell'elemento visivo
        if (!puoPrenotare) {
            let spanOcc = document.createElement("span");
            spanOcc.classList.add("btn-time", "occupied");
            spanOcc.style.cssText = "background:#f2f2f2; color:#999; border:1px solid #ddd; padding:10px; text-align:center; border-radius:5px;";
            spanOcc.innerText = "Occupato";
            divOrari.appendChild(spanOcc);
        
        } else if (haPrenotazioneAttiva) {
            let spanBloc = document.createElement("span");
            spanBloc.classList.add("btn-time", "disabled");
            spanBloc.style.cssText = "background:#fdfdfe; color:#e0e0e0; border:1px solid #eee; padding:10px; text-align:center; border-radius:5px;";
            spanBloc.innerText = "Bloccato";
            divOrari.appendChild(spanBloc);
        
        } else {
            let linkLibero = document.createElement("a");
            linkLibero.classList.add("btn-time");
            linkLibero.style.cssText = "display:block; padding:10px; background:#3498db; color:white; text-align:center; text-decoration:none; border-radius:5px; font-weight:bold;";
            linkLibero.innerText = oraSlot;
            linkLibero.href = "conferma_prenotazione.php?service_id=" + serviceId + "&data=" + dataSelezionata + "&ora=" + oraSlot;
            divOrari.appendChild(linkLibero);
        }
    }
}

function setup() {
    if (dataSelezionata !== null) {
        // Nasconde il calendario e trasforma l'intestazione
        const nav = document.querySelector(".calendar-nav");
        if (nav) {
            nav.innerHTML = "<a href='prenota.php?service_id=" + serviceId + "&m=" + mese + "&y=" + anno + "' style='font-weight: bold; text-decoration: underline;'>Cambia giorno</a>";
            nav.style.textAlign = "center";
            nav.style.display = "block";
        }
        
        const cal = document.getElementById("calendario");
        if (cal) cal.style.display = "none";
        
        // Disegna la lista orari
        if (document.getElementById("orari-container")) {
            generaOrari();
        }
        
    } else {
        // Nessuna data selezionata: mostra il calendario e nasconde gli orari (se per caso ci sono)
        if (document.getElementById("calendario")) {
            generaTabella();
        }
    }
}

document.addEventListener('DOMContentLoaded', setup);