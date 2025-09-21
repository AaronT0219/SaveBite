// Food Card animation handler
document.addEventListener('DOMContentLoaded', function() {
  // Hide all btn-containers by default
  document.querySelectorAll('.btn-container').forEach(function(btn) {
    btn.classList.add('hidden-btn');
  });

  // Add click event to each food-card
  document.querySelectorAll('.food-card').forEach(function(card) {
    card.addEventListener('click', function(e) {
      // Hide all other btn-containers
      document.querySelectorAll('.btn-container').forEach(function(btn) {
        btn.classList.add('hidden-btn');
        btn.classList.remove('show-btn');
      });
      // Show the btn-container inside this card
      var btnContainer = card.querySelector('.btn-container');
      if(btnContainer) {
        btnContainer.classList.remove('hidden-btn');
        btnContainer.classList.add('show-btn');
      }
      e.stopPropagation();
    });
  });

  // Hide btn-container if click outside
  document.addEventListener('click', function(e) {
    document.querySelectorAll('.btn-container').forEach(function(btn) {
      btn.classList.add('hidden-btn');
      btn.classList.remove('show-btn');
    });
  });
});
