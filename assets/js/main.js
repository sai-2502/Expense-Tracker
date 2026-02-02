// main.js - small helper + chart rendering

// render category chart if data present
if(typeof categoryLabels !== 'undefined' && categoryLabels.length){
  const ctx = document.getElementById('categoryChart');
  if(ctx){
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: categoryLabels,
        datasets: [{ data: categoryData }]
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
      }
    });
  }
}

// small helpers can be added here for editing, ajax etc.