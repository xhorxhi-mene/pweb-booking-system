document.addEventListener('DOMContentLoaded', () => {
    
    const alerts = document.querySelectorAll('.alert, [style*="background-color: #d4edda"]');
    
    alerts.forEach((alert) => {
        setTimeout(() => {
            alert.style.transition = "opacity 0.6s ease";
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 600);
        }, 5000);
    });

    // scorrimento fluido
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', (e) => {
            e.preventDefault();
            // Uso 'anchor' invece di 'this' perché siamo in un'arrow function
            const targetId = anchor.getAttribute('href');
            
            // Controllo di sicurezza: evita errori se l'href è solo "#" o vuoto
            if (targetId !== "#" && targetId !== "") {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // effetto feedback pulsante
    const btnPrenota = document.querySelector('.btn-prenota');
    if (btnPrenota) {
        // Uso una 'function()' standard in modo che 'this' faccia riferimento al bottone
        btnPrenota.addEventListener('click', function() {
            this.style.transform = "scale(0.95)";
            this.style.backgroundColor = "#2980b9";
            
            setTimeout(() => {
                this.style.transform = "scale(1)";
            }, 150);
        });
    }

    console.log("Sistema Prenotazioni Salone caricato correttamente.");
});