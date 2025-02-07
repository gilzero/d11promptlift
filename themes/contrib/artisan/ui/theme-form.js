(function (Drupal, once, window) {

  Drupal.artisan_theme_form = {};

  Drupal.behaviors.artisan_theme_form = {
    attach(context) {
      // Color & palette.
      once('artisan-theme-form-color-field', '.artisan-theme-form-group input[type="color"]', context).forEach(colorField);
      once('artisan-theme-form-color-palette', '.artisan-theme-form-group.artisan-theme-form-group--palette input[type="color"]', context).forEach(paletteColorsDef);
      once('artisan-theme-form-color-palette', '.artisan-theme-form-group:not(.artisan-theme-form-group--palette) input[type="color"]', context).forEach(paletteColorsSet);

      // Extra widgets.
      once('artisan-theme-form-extra-widget', '.artisan-theme-form-group input.artisan-theme-extra-widget-numeric-input', context).forEach(numericInput);
      once('artisan-theme-form-extra-widget', '.artisan-theme-form-group input.artisan-theme-extra-widget-numeric-unit', context).forEach(numericUnit);
      once('artisan-theme-form-extra-widget', '.artisan-theme-form-group input.artisan-theme-extra-widget-font-weight', context).forEach(fontWeight);
      once('artisan-theme-form-extra-widget', '.artisan-theme-form-group input.artisan-theme-extra-widget-decoration', context).forEach(decoration);

      // Presets.
      once('artisan-theme-presets', '.artisan-theme-form-presets', context).forEach(initPresets);
    }
  };

  function decoration(element) {
    const deCoptions = new Map();
    deCoptions.set('_empty', Drupal.t('- Decoration select / Clear -'));
    deCoptions.set('underline', Drupal.t('Underline'));
    deCoptions.set('line-through', Drupal.t('Line-through'));
    deCoptions.set('overline', Drupal.t('Overline'));
    deCoptions.set('_other', Drupal.t('- Other -'));

    let deCwrapper = document.createElement('div');
    deCwrapper.classList.add('artisan-theme-form__widget-extra-wrapper');

    let deCselect = document.createElement('select');
    deCselect.classList.add('form-element', 'form-element--type-select');
    for (let [currentValue, currentLabel] of deCoptions) {
      let optionElement = document.createElement('option');
      optionElement.value = currentValue;
      optionElement.textContent = currentLabel;
      deCselect.appendChild(optionElement);
    };

    element.parentNode.classList.add('artisan-theme-form__autofocus');
    element.classList.add('artisan-theme-form__influenced');
    element.readOnly = true;

    deCselect.addEventListener("change", (event) => {
      if (event.target.value === '_other') {
        element.readOnly = false;
        element.focus();
      }
      else if (event.target.value === '_empty') {
        element.readOnly = true;
        element.value = '';
      }
      else {
        element.value = event.target.value;
        element.readOnly = true;
      }
    });

    element.addEventListener("focus", (event) => {
      if (event.target.readOnly) {
        deCselect.focus();
      }
    });

    deCwrapper.appendChild(deCselect);
    element.parentNode.appendChild(deCwrapper);

    if (element.value !== '') {
      deCselect.value = element.value;
      if (deCselect.value === '') {
        deCselect.value = '_other';
        element.readOnly = false;
      }
    }
  }

  function fontWeight(element) {
    const fWoptions = new Map();
    fWoptions.set('_empty', Drupal.t('- Weight select / Clear -'));
    fWoptions.set('lighter', Drupal.t('Lighter'));
    fWoptions.set('normal', Drupal.t('Normal'));
    fWoptions.set('bold', Drupal.t('Bold'));
    fWoptions.set('bolder', Drupal.t('Bolder'));
    fWoptions.set('100', Drupal.t('100 - Thin'));
    fWoptions.set('200', Drupal.t('200 - Extra Light'));
    fWoptions.set('300', Drupal.t('300 - Light'));
    fWoptions.set('400', Drupal.t('400 - Normal'));
    fWoptions.set('500', Drupal.t('500 - Medium'));
    fWoptions.set('600', Drupal.t('600 - Semi Bold'));
    fWoptions.set('700', Drupal.t('700 - Bold'));
    fWoptions.set('800', Drupal.t('800 - Extra Bold'));
    fWoptions.set('800', Drupal.t('900 - Black'));
    fWoptions.set('_other', Drupal.t('- Other -'));

    let fWwrapper = document.createElement('div');
    fWwrapper.classList.add('artisan-theme-form__widget-extra-wrapper');

    let fWselect = document.createElement('select');
    fWselect.classList.add('form-element', 'form-element--type-select');
    for (let [currentValue, currentLabel] of fWoptions) {
      let optionElement = document.createElement('option');
      optionElement.value = currentValue;
      optionElement.textContent = currentLabel;
      fWselect.appendChild(optionElement);
    };

    element.parentNode.classList.add('artisan-theme-form__autofocus');
    element.classList.add('artisan-theme-form__influenced');
    element.readOnly = true;

    fWselect.addEventListener("change", (event) => {
      if (event.target.value === '_other') {
        element.readOnly = false;
        element.focus();
      }
      else if (event.target.value === '_empty') {
        element.readOnly = true;
        element.value = '';
      }
      else {
        element.value = event.target.value;
        element.readOnly = true;
      }
    });

    element.addEventListener("focus", (event) => {
      if (event.target.readOnly) {
        fWselect.focus();
      }
    });

    fWwrapper.appendChild(fWselect);
    element.parentNode.appendChild(fWwrapper);

    if (element.value !== '') {
      fWselect.value = element.value;
      if (fWselect.value === '') {
        fWselect.value = '_other';
        element.readOnly = false;
      }
    }
  }

  function numericInput(element) {
    element.type = "number";
    element.min = "0";
    element.step = "0.1";
  }

  function numericUnit(element) {
    const unitOptions = {
      '_empty': Drupal.t('- Unit select / Clear -'),
      'px': Drupal.t('px - pixels'),
      'em': Drupal.t('em - relative to parent element'),
      'rem': Drupal.t('rem - relative to root/browser element'),
      'vw': Drupal.t('vw - relative to window width'),
      'vh': Drupal.t('vh - relative to window heigth'),
      '%': Drupal.t('% - percentage relative to parent element'),
      '_other': Drupal.t('- Other -')
    };
    let numericUnitWrapper = document.createElement('div');
    numericUnitWrapper.classList.add('artisan-theme-form__widget-extra-wrapper');
    let unitSelect = document.createElement('select');
    unitSelect.classList.add('form-element', 'form-element--type-select');
    Object.keys(unitOptions).forEach((currentValue) => {
      let optionElement = document.createElement('option');
      optionElement.value = currentValue;
      optionElement.textContent = unitOptions[currentValue];
      unitSelect.appendChild(optionElement);
    });

    let numericInput = document.createElement('input');
    numericInput.classList.add('form-element');
    numericInput.type = 'number';
    numericInput.min = '0';
    numericInput.step = '0.1';

    numericInput.addEventListener("change", (event) => {
      element.value = event.target.value + (unitSelect.value !== '_empty' && unitSelect.value !== '_other' ? unitSelect.value : '');
    });

    element.classList.add('artisan-theme-form__influenced');
    element.parentNode.classList.add('artisan-theme-form__autofocus');

    unitSelect.addEventListener("change", (event) => {
      if (event.target.value === '_other') {
        element.readOnly = false;
        element.focus();
        numericInput.classList.add('visually-hidden');
      }
      else if (event.target.value === '_empty') {
        element.readOnly = true;
        numericInput.value = '';
        numericInput.dispatchEvent(new Event('change'));
        numericInput.classList.remove('visually-hidden');
      }
      else {
        element.value = numericInput.value + event.target.value;
        element.readOnly = true;
        numericInput.classList.remove('visually-hidden');
      }
    });

    element.readOnly = true;
    numericUnitWrapper.appendChild(numericInput);
    numericUnitWrapper.appendChild(unitSelect);
    element.parentNode.appendChild(numericUnitWrapper);

    element.addEventListener("focus", (event) => {
      if (event.target.readOnly) {
        numericInput.focus();
      }
    });
    if (element.value !== '') {
      numericInput.value = element.value.replace(/[a-zA-Z]/g, '');
      unitSelect.value = element.value.replace(/[0-9\.,]/g, '');
      if (unitSelect.value === '') {
        numericInput.classList.add('visually-hidden');
        unitSelect.value = '_other';
      }
    }
  }

  function paletteColorsDef(paletteElement) {
    Drupal.artisan_theme_form[paletteElement.name] = {
      'label': paletteElement.parentNode.querySelector('label').textContent,
      'value': paletteElement.value.toUpperCase()
    };
    paletteElement.addEventListener("change", (event) => {
      Drupal.artisan_theme_form[paletteElement.name].value = event.target.value.toUpperCase();
      paletteElement.closest('form').querySelectorAll('.artisan-theme-form-group:not(.artisan-theme-form-group--palette) select').forEach((element) => {
        if (element.querySelector('option[value="'+ event.target.name +'"]')) {
          element.querySelector('option[value="'+ event.target.name +'"]').disabled = paletteElement.dataset.empty === event.target.value;
        }
        if (element.value === event.target.name) {
          element.parentNode.parentNode.querySelector('input[type="color"]').value = event.target.value;
          element.dispatchEvent(new Event('change'));
        }
      });
    });
  }

  function paletteColorsSet(element) {

    let paletteWrapper = document.createElement('div');
    paletteWrapper.classList.add('artisan-theme-form__widget-extra-wrapper');
    let paletteSelect = document.createElement('select');
    paletteSelect.classList.add('form-element', 'form-element--type-select');
    let paletteSelectOptionEmpty = document.createElement('option');
    paletteSelectOptionEmpty.value = '_empty';
    paletteSelectOptionEmpty.textContent = Drupal.t('- Palette select -');
    paletteSelect.appendChild(paletteSelectOptionEmpty);
    Object.keys(Drupal.artisan_theme_form).forEach((currentValue) => {
      let paletteSelectOption = document.createElement('option');
      if (Drupal.artisan_theme_form[currentValue].value === element.dataset.empty) {
        paletteSelectOption.disabled = true;
      }
      paletteSelectOption.value = currentValue;
      paletteSelectOption.textContent = Drupal.artisan_theme_form[currentValue].label;
      paletteSelect.appendChild(paletteSelectOption);
    });
    let paletteSelectOptionOther = document.createElement('option');
    paletteSelectOptionOther.value = '_other';
    paletteSelectOptionOther.textContent = Drupal.t('- Other -');
    paletteSelect.appendChild(paletteSelectOptionOther);
    paletteSelect.addEventListener("change", (event) => {
      if (event.target.value !== '_other' && event.target.value !== '_empty') {
        event.target.parentNode.parentNode.querySelector('input[type="color"]').value = Drupal.artisan_theme_form[event.target.value].value;
        event.target.parentNode.parentNode.querySelector('input[type="color"]').dispatchEvent(new Event('change'));
      }
      else if (event.target.value === '_other') {
        event.target.parentNode.parentNode.querySelector('input[type="color"]').click();
      }
      else if (event.target.value === '_empty') {
        event.target.parentNode.parentNode.querySelector('a.artisan-theme-form-color-clear').click();
      }
    });

    paletteWrapper.appendChild(paletteSelect);
    element.parentNode.appendChild(paletteWrapper);

//      element.parentNode.appendChild(paletteSelect);

    element.addEventListener("change", (event) => {
      let paletteActive = false;
      event.target.parentNode.querySelector('select').value = '_other';
      Object.keys(Drupal.artisan_theme_form).forEach((currentValue) => {
        if (!paletteActive && event.target.value !== event.target.dataset.empty && Drupal.artisan_theme_form[currentValue].value === event.target.value.toUpperCase()) {
          event.target.parentNode.querySelector('select').value = currentValue;
          paletteActive = true;
        }
      });
      if (!paletteActive && event.target.value === event.target.dataset.empty) {
        event.target.parentNode.querySelector('select').value = '_empty';
      }
      else if (!paletteActive) {
        event.target.parentNode.querySelector('select').value = '_other';
      }
    });
    element.dispatchEvent(new Event('change'));
  }

  /**
   * Color field.
   */
  function colorField(element) {
    let clearLink = document.createElement('a');
    clearLink.href = '#';
    clearLink.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      element.value = element.dataset.empty;
      element.dispatchEvent(new Event('change'));
    });
    clearLink.classList.add('artisan-theme-form-color-clear');
    clearLink.appendChild(document.createTextNode(Drupal.t('Clear')));
    element.parentNode.insertBefore(clearLink, element.nextSibling);

    if (element.value === element.dataset.empty) {
      clearLink.classList.add('artisan-theme-form-color-clear--cleared');
    }
    element.addEventListener('change', function (e) {
      if (e.target.value === e.target.dataset.empty) {
        clearLink.classList.add('artisan-theme-form-color-clear--cleared');
      }
      else {
        clearLink.classList.remove('artisan-theme-form-color-clear--cleared');
      }
    });
  }

  function initPresets(element) {
    function snake_case_string(str) {
      return str && str.match(
        /[A-Z]{2,}(?=[A-Z][a-z]+[0-9]*|\b)|[A-Z]?[a-z]+[0-9]*|[A-Z]|[0-9]+/g)
        .map(s => s.toLowerCase())
        .join('_');
    }
    let presets = {};
    if (element.value) {
      try {
        presets = JSON.parse(element.value);
      }
      catch (error) {
        const messages = new Drupal.Message();
        messages.clear();
        messages.add(Drupal.t('No valid presets stored, it will be reset on save (any new preset will be preserved). Invalid JSON format:<pre>:json</pre>', {':json': element.value}), {type: 'warning'});
        presets = {};
        element.value = '';
        // element.classList.remove('visually-hidden');
      }
    }

    presets['clear'] = {
      'label': Drupal.t('- Clear everything, use wisely! -'),
      'keys': {}
    };

    element.closest('.artisan-theme-form-presets-wrapper').querySelector('div[class*="description"] a').addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      element.classList.toggle('visually-hidden');
    });

    let presetWrapper = document.querySelector('.artisan-theme-form-presets-wrapper');
    let presetInnerWrapper = document.createElement('div');
    presetInnerWrapper.classList.add('artisan-theme-form-presets-inner-wrapper');
    let presetsSelect = document.createElement('select');

    let presetsAddButton = document.createElement('button');
    let presetsApplyButton = document.createElement('button');
    let presetsRemoveButton = document.createElement('button');
    let addPresetText = document.createTextNode(Drupal.t('Create preset (current snap)'));
    let applyPresetText = document.createTextNode(Drupal.t('Apply / preview selected preset'));
    let removePresetText = document.createTextNode("Remove selected preset");
    let inputPresetName = document.createElement('input');
    inputPresetName.type = 'text';
    inputPresetName.classList.add('form-element');
    inputPresetName.placeholder = 'New preset name';
    presetsApplyButton.appendChild(applyPresetText);
    presetsApplyButton.classList.add('button');
    presetsRemoveButton.appendChild(removePresetText);
    presetsRemoveButton.classList.add('button', 'button--danger');
    presetsAddButton.appendChild(addPresetText);
    presetsAddButton.classList.add('button');
    refreshSelectOptions();
    presetsSelect.classList.add('form-element', 'form-element--type-select');
    presetWrapper.appendChild(presetInnerWrapper);
    presetInnerWrapper.appendChild(presetsSelect);
    presetInnerWrapper.appendChild(presetsApplyButton);
    presetInnerWrapper.appendChild(presetsRemoveButton);
    presetInnerWrapper.appendChild(inputPresetName);
    presetInnerWrapper.appendChild(presetsAddButton);

    presetsApplyButton.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();
      const messages = new Drupal.Message();
      messages.clear();
      if (presets[presetsSelect.value] === undefined) {
        messages.add(Drupal.t('Please select a preset to apply it'), {type: 'error'});
        return;
      }

      event.target.closest('form').querySelectorAll('.artisan-theme-form-group input[data-drupal-selector][name], .artisan-theme-form-group select[data-drupal-selector][name], .artisan-theme-form-group textarea[data-drupal-selector][name]').forEach((element) => {
        if (presets[presetsSelect.value] && presets[presetsSelect.value].keys && presets[presetsSelect.value].keys[element.name] !== undefined) {
          if (element.type === 'checkbox') {
            element.checked = presets[presetsSelect.value].keys[element.name];
          }
          else {
            element.value = presets[presetsSelect.value].keys[element.name];
          }
        }
        else {
          if (element.type === 'checkbox') {
            element.checked = false;
          }
          else {
            element.value = element.dataset.empty || '';
          }
        }
        element.dispatchEvent(new Event('change'));
      });


      messages.add(Drupal.t('Preset applied, please check preset modifications before consolidation by saving.'), {type: 'warning'});
    });

    presetsRemoveButton.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();
      const messages = new Drupal.Message();
      messages.clear();
      if (!presetsSelect.value || presetsSelect.value === 'clear' || presets[presetsSelect.value] === undefined) {
        messages.add(Drupal.t('Please select a preset to remove it'), {type: 'error'});
      }
      else {
        messages.add(Drupal.t('Preset :label removed', {':label': presets[presetsSelect.value].label}), {type: 'warning'});
        delete presets[presetsSelect.value];
        if (presets['clear']) {
          delete presets['clear'];
        }
        element.value = JSON.stringify(presets);
        presets['clear'] = {
          'label': Drupal.t('- Clear everything, use wisely! -'),
          'keys': {}
        };
        refreshSelectOptions();
      }
    });

    presetsAddButton.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();
      const messages = new Drupal.Message();
      messages.clear();

      if (Object.keys(presets).length > 10) {
        messages.add(Drupal.t('Maximum preset reached (10), please remove one to add a new one'), {type: 'error'});
        inputPresetName.value = '';
        return;
      }
      let presetKey = snake_case_string(inputPresetName.value) ||  'preset_' + (Object.keys(presets).length + 1);
      let presetLabel = inputPresetName.value || 'Preset ' + (Object.keys(presets).length + 1);
      presets[presetKey] = {
        'label': presetLabel,
        'keys': {}
      };
      event.target.closest('form').querySelectorAll('.artisan-theme-form-group input[data-drupal-selector][name], .artisan-theme-form-group select[data-drupal-selector][name], .artisan-theme-form-group textarea[data-drupal-selector][name]').forEach((element) => {
        if (element.type === 'checkbox') {
          if (element.checked) {
            presets[presetKey]['keys'][element.name] = element.checked;
          }
        }
        else if (element.value !== '' && element.value !== element.dataset.empty) {
          presets[presetKey]['keys'][element.name] = element.value;
        }
      });
      inputPresetName.value = '';
      messages.add(Drupal.t('New preset :label created, consolidate by saving', {':label': presetLabel}), {type: 'warning'});
      if (presets['clear']) {
        delete presets['clear'];
      }
      element.value = JSON.stringify(presets);
      presets['clear'] = {
        'label': Drupal.t('- Clear everything, use wisely! -'),
        'keys': {}
      };
      refreshSelectOptions();
    });

    function refreshSelectOptions() {
      presetsSelect.innerHTML = '';
      let defaultOption = document.createElement('option');
      defaultOption.value = '_empty';
      defaultOption.textContent = Drupal.t('- Preset select -');
      presetsSelect.appendChild(defaultOption);

      Object.keys(presets).forEach((currentValue) => {
        let optionElement = document.createElement('option');
        optionElement.value = currentValue;
        optionElement.textContent = presets[currentValue].label;
        presetsSelect.appendChild(optionElement);
      });
    }
  }
}(Drupal, once, window));
