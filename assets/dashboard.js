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
});
