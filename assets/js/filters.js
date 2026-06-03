(function(){

  // ── 1) Layout Estado standalone (dropdown minimalista) ──────────────────────
  document.querySelectorAll('.imo-state-filter[data-scope]').forEach(function(filter){
    var trigger  = filter.querySelector('.imo-state-trigger');
    var dropdown = filter.querySelector('.imo-state-dropdown');
    var label    = filter.querySelector('.imo-state-label');
    var archive  = filter.getAttribute('data-archive') || window.location.pathname;
    if(!trigger || !dropdown || !label) return;

    function openState(){
      filter.classList.add('imo-state-open');
      trigger.setAttribute('aria-expanded','true');
    }
    function closeState(){
      filter.classList.remove('imo-state-open');
      trigger.setAttribute('aria-expanded','false');
    }

    trigger.addEventListener('click', function(e){
      e.stopPropagation();
      filter.classList.contains('imo-state-open') ? closeState() : openState();
    });

    dropdown.querySelectorAll('li').forEach(function(item){
      item.addEventListener('click', function(){
        label.textContent = item.textContent.trim();
        closeState();
        var slug = item.getAttribute('data-slug');
        window.location.href = slug ? archive + '?estado=' + encodeURIComponent(slug) : archive;
      });
    });

    document.addEventListener('click', function(e){
      if(!filter.contains(e.target)) closeState();
    });
    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape') closeState();
    });
  });


  // ── 2) Layout Full — dropdowns customizados + autocomplete ──────────────────
  document.querySelectorAll('.imo-rn-filter[data-scope]').forEach(function(wrapper){
    var form = wrapper.closest('form');

    // 2a) Dropdowns customizados (cidade, status, estado)
    wrapper.querySelectorAll('.imo-dropdown').forEach(function(dd){
      var trigger = dd.querySelector('.imo-dropdown-trigger');
      var ddList  = dd.querySelector('.imo-dropdown-list');
      var hidden  = dd.querySelector('input[type="hidden"]');
      var label   = dd.querySelector('.imo-dropdown-label');
      if(!trigger || !ddList || !hidden || !label) return;

      function closeAllOthers(){
        wrapper.querySelectorAll('.imo-dropdown.open').forEach(function(other){
          if(other === dd) return;
          other.classList.remove('open');
          var t = other.querySelector('.imo-dropdown-trigger');
          if(t) t.setAttribute('aria-expanded','false');
        });
      }

      function openDd(){
        closeAllOthers();
        // fecha autocomplete se estiver visível
        var ac = wrapper.querySelector('.rn-autocomplete');
        if(ac){ ac.innerHTML = ''; ac.style.display = 'none'; }
        dd.classList.add('open');
        trigger.setAttribute('aria-expanded','true');
      }

      function closeDd(){
        dd.classList.remove('open');
        trigger.setAttribute('aria-expanded','false');
      }

      trigger.addEventListener('click', function(e){
        e.stopPropagation();
        dd.classList.contains('open') ? closeDd() : openDd();
      });

      ddList.querySelectorAll('li').forEach(function(item){
        item.addEventListener('click', function(){
          ddList.querySelectorAll('li.selected').forEach(function(s){ s.classList.remove('selected'); });
          item.classList.add('selected');
          hidden.value = item.getAttribute('data-value');
          label.textContent = item.textContent.trim();
          closeDd();
          if(form) form.submit();
        });
      });

      document.addEventListener('click', function(e){
        if(!dd.contains(e.target)) closeDd();
      });
      document.addEventListener('keydown', function(e){
        if(e.key === 'Escape') closeDd();
      });
    });

    // 2b) Autocomplete (requer imoFilters localizado pelo PHP)
    if(typeof imoFilters === 'undefined') return;

    var input  = wrapper.querySelector('.rn-search');
    var acList = wrapper.querySelector('.rn-autocomplete');
    if(!input || !acList) return;

    var timer = null;

    function closeAc(){
      acList.innerHTML = '';
      acList.style.display = 'none';
    }

    function closeAllDropdowns(){
      wrapper.querySelectorAll('.imo-dropdown.open').forEach(function(dd){
        dd.classList.remove('open');
        var t = dd.querySelector('.imo-dropdown-trigger');
        if(t) t.setAttribute('aria-expanded','false');
      });
    }

    function populateAc(items){
      acList.innerHTML = '';
      if(!items || !items.length){ closeAc(); return; }
      items.forEach(function(item){
        var li = document.createElement('li');
        li.setAttribute('role','option');

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
        acList.appendChild(li);
      });
      acList.style.display = 'block';
    }

    input.addEventListener('input', function(){
      clearTimeout(timer);
      closeAllDropdowns();
      var term = input.value.trim();
      if(term.length < 3){ closeAc(); return; }
      timer = setTimeout(function(){
        var url = imoFilters.ajaxurl
          + '?action=imo_suggest'
          + '&nonce=' + encodeURIComponent(imoFilters.nonce)
          + '&term='  + encodeURIComponent(term);
        fetch(url)
          .then(function(r){ return r.json(); })
          .then(populateAc)
          .catch(closeAc);
      }, 250);
    });

    input.addEventListener('keydown', function(e){
      if(e.key === 'Escape') closeAc();
    });

    document.addEventListener('click', function(e){
      if(!wrapper.contains(e.target)) closeAc();
    });
  });

})();
