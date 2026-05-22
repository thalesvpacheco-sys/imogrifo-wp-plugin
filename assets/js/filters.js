(function(){
  // 1) Join multiple selects em hidden antes do submit
  document.addEventListener('submit', function(e){
    var form = e.target.closest('.imo-filters-form');
    if(!form) return;
    form.querySelectorAll('input[type="hidden"][data-join-multiple="1"]').forEach(function(hidden){
      var name = hidden.getAttribute('name');
      var select = form.querySelector('select[name="'+name+'_tmp[]"]');
      if(!select) return;
      var values = Array.from(select.selectedOptions).map(function(o){ return o.value; }).filter(Boolean);
      hidden.value = values.join(',');
    });
  }, true);

  // 2) Autocomplete + auto-submit de selects
  if(typeof imoFilters === 'undefined') return;

  document.querySelectorAll('.imo-rn-filter[data-scope]').forEach(function(wrapper){
    var input = wrapper.querySelector('.rn-search');
    var list  = wrapper.querySelector('.rn-autocomplete');
    var form  = wrapper.closest('form');
    if(!input || !list) return;

    var timer = null;

    function closeList(){
      list.innerHTML = '';
      list.style.display = 'none';
    }

    function populateList(items){
      list.innerHTML = '';
      if(!items || !items.length){ closeList(); return; }
      items.forEach(function(item){
        var li = document.createElement('li');
        li.setAttribute('role', 'option');

        var title = document.createElement('span');
        title.className = 'imo-suggest-title';
        title.textContent = item.label;
        li.appendChild(title);

        var meta = [item.cidade, item.status].filter(Boolean).join(' · ');
        if(meta){
          var sub = document.createElement('span');
          sub.className = 'imo-suggest-meta';
          sub.textContent = meta;
          li.appendChild(sub);
        }

        li.addEventListener('mousedown', function(e){
          e.preventDefault();
          window.location.href = item.link;
        });
        list.appendChild(li);
      });
      list.style.display = 'block';
    }

    input.addEventListener('input', function(){
      clearTimeout(timer);
      var term = input.value.trim();
      if(term.length < 3){ closeList(); return; }
      timer = setTimeout(function(){
        var url = imoFilters.ajaxurl
          + '?action=imo_suggest'
          + '&nonce=' + encodeURIComponent(imoFilters.nonce)
          + '&term='  + encodeURIComponent(term);
        fetch(url)
          .then(function(r){ return r.json(); })
          .then(populateList)
          .catch(closeList);
      }, 250);
    });

    input.addEventListener('keydown', function(e){
      if(e.key === 'Escape') closeList();
    });

    document.addEventListener('click', function(e){
      if(!wrapper.contains(e.target)) closeList();
    });

    // Auto-submit ao mudar select — sem precisar clicar na lupa
    if(form){
      wrapper.querySelectorAll('select').forEach(function(sel){
        sel.addEventListener('change', function(){
          form.submit();
        });
      });
    }
  });
})();
