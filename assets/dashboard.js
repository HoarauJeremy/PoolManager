import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', function() {
    // Configuration du graphique des interventions par mois
    const interventionsCtx = document.getElementById('interventionsChart');
    if (interventionsCtx && typeof statsData !== 'undefined') {
        new Chart(interventionsCtx, {
            type: 'line',
            data: {
                labels: statsData.interventions_par_mois.labels,
                datasets: [{
                    label: 'Interventions',
                    data: statsData.interventions_par_mois.data,
                    borderColor: 'rgb(79, 70, 229)',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgb(79, 70, 229)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 5
                        }
                    }
                }
            }
        });
    }

    // Configuration du graphique des types d'interventions
    const typesCtx = document.getElementById('typesChart');
    if (typesCtx && typeof statsData !== 'undefined') {
        new Chart(typesCtx, {
            type: 'doughnut',
            data: {
                labels: statsData.interventions_par_type.labels,
                datasets: [{
                    data: statsData.interventions_par_type.data,
                    backgroundColor: [
                        'rgba(79, 70, 229, 0.8)',   // Indigo
                        'rgba(234, 179, 8, 0.8)',   // Jaune
                        'rgba(34, 197, 94, 0.8)',   // Vert
                        'rgba(239, 68, 68, 0.8)'    // Rouge
                    ],
                    borderColor: [
                        'rgb(79, 70, 229)',
                        'rgb(234, 179, 8)',
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Configuration du graphique des interventions par mois pour l'utilisateur
    const userInterventionsCtx = document.getElementById('userInterventionsChart');
    if (userInterventionsCtx && typeof statsData !== 'undefined') {
        new Chart(userInterventionsCtx, {
            type: 'line',
            data: {
                labels: statsData.interventions_par_mois.labels,
                datasets: [{
                    label: 'Mes interventions',
                    data: statsData.interventions_par_mois.data,
                    borderColor: 'rgb(220, 38, 38)',
                    backgroundColor: 'rgba(220, 38, 38, 0.1)',
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: 'rgb(220, 38, 38)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Gestion de la pagination AJAX
    setupAjaxPagination();
});

function setupAjaxPagination() {
    const paginationLinks = document.querySelectorAll('nav a[href*="page="]');

    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.href;
            const page = new URL(url).searchParams.get('page');

            // Ajouter une classe de chargement
            const tableContainer = document.querySelector('.overflow-x-auto');
            if (tableContainer) {
                tableContainer.style.opacity = '0.5';
                tableContainer.style.pointerEvents = 'none';
            }

            // Charger la nouvelle page
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                // Parser le HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Extraire le contenu de la table et la pagination
                const newTable = doc.querySelector('.overflow-x-auto');
                const newPagination = doc.querySelector('nav.flex');
                const currentTable = document.querySelector('.overflow-x-auto');
                const currentPagination = document.querySelector('nav.flex');

                if (newTable && currentTable) {
                    currentTable.innerHTML = newTable.innerHTML;
                    currentTable.style.opacity = '1';
                    currentTable.style.pointerEvents = 'auto';
                }

                if (newPagination && currentPagination) {
                    currentPagination.innerHTML = newPagination.innerHTML;
                    // Réattacher les événements après le remplacement
                    setupAjaxPagination();
                }

                // Scroll vers le haut de la section
                window.scrollTo({
                    top: document.querySelector('.bg-white.rounded-lg.shadow').offsetTop - 100,
                    behavior: 'smooth'
                });
            })
            .catch(error => {
                console.error('Erreur lors du chargement de la page:', error);
                // En cas d'erreur, rediriger normalement
                window.location.href = url;
            });
        });
    });
}
