(function () {
  'use strict';

  // Society list filter (home page)
  var sf = document.getElementById('socfilter');
  if (sf) {
    var cards = Array.prototype.slice.call(document.querySelectorAll(sf.getAttribute('data-filter-target')));
    var empty = document.getElementById('socempty');
    sf.addEventListener('input', function () {
      var q = sf.value.trim().toLowerCase(), shown = 0;
      cards.forEach(function (c) {
        var hit = !q || (c.getAttribute('data-search') || '').indexOf(q) !== -1;
        c.hidden = !hit;
        if (hit) shown++;
      });
      if (empty) empty.hidden = shown !== 0;
    });
  }

  // Society page: category chips + live search
  var filter = document.querySelector('[data-provider-filter]');
  var sections = Array.prototype.slice.call(document.querySelectorAll('[data-cat-section]'));
  if (sections.length) {
    var chips = Array.prototype.slice.call(document.querySelectorAll('.chip'));
    var pempty = document.getElementById('pempty');
    var activeCat = 'all';

    function apply() {
      var q = filter ? filter.value.trim().toLowerCase() : '';
      var words = q ? q.split(/\s+/) : [];
      var anyShown = false;
      sections.forEach(function (sec) {
        var inCat = activeCat === 'all' || sec.getAttribute('data-cat-section') === activeCat;
        var visible = 0;
        Array.prototype.forEach.call(sec.querySelectorAll('.pcard'), function (card) {
          var hay = (card.getAttribute('data-search') || '') + ' ' + (sec.querySelector('h2').textContent || '').toLowerCase();
          var ok = inCat && words.every(function (w) { return hay.indexOf(w) !== -1; });
          card.hidden = !ok;
          if (ok) visible++;
        });
        sec.hidden = visible === 0;
        if (visible) anyShown = true;
      });
      if (pempty) pempty.hidden = anyShown;
    }

    chips.forEach(function (chip) {
      chip.addEventListener('click', function () {
        activeCat = chip.getAttribute('data-cat');
        chips.forEach(function (c) { c.classList.toggle('is-on', c === chip); });
        apply();
        var first = document.querySelector('.listing');
        if (first && window.scrollY > first.offsetTop) first.scrollIntoView({ block: 'start' });
      });
    });
    if (filter) filter.addEventListener('input', apply);
  }

  // Login: only digits in mobile / otp
  ['mobile', 'otp'].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('input', function () { el.value = el.value.replace(/\D/g, ''); });
  });
})();
