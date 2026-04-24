(function(){
  // 1) Join multiple selects em hidden antes do submit
  document.addEventListener('submit', function(e){
    var form = e.target.closest('.imo-filters');
    if(!form) return;

    // Para cada hidden data-join-multiple, procure o select TMP correspondente
    form.querySelectorAll('input[type="hidden"][data-join-multiple="1"]').forEach(function(hidden){
      var name = hidden.getAttribute('name');
      var select = form.querySelector('select[name="'+name+'_tmp[]"]');
      if(!select) return;
      var values = Array.from(select.selectedOptions).map(function(o){ return o.value; }).filter(Boolean);
      hidden.value = values.join(',');
    });
  }, true);
})();
