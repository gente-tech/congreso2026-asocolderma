(function (Drupal, once) {

	function getSubmitButton(form) {
		if (!form) return null;

		return form.querySelector(
			'[data-drupal-selector="edit-submit-dermau-docentes"], .js-form-submit, .form-submit'
		);
	}

	function triggerDrupalAjax(form) {
		const submitButton = getSubmitButton(form);
		if (submitButton) {
			submitButton.click();
		}
	}

	function closeAllProgramFilters(scope) {
		(scope || document)
			.querySelectorAll('.du-seach__content .du-filter-down[data-target]')
			.forEach(function (filter) {
				filter.classList.remove('active');
				filter.classList.remove('open');
			});
	}

	function refreshProgramSwiper() {
		const grid = document.querySelector('.du-programs-grid');
		const slider = document.querySelector('.du-swiper-program .swiper');

		if (window.duSwiperProgram && typeof window.duSwiperProgram.destroy === 'function') {
			window.duSwiperProgram.destroy(true, true);
			window.duSwiperProgram = null;
		}

		if (grid) {
			return;
		}

		if (slider && typeof window.initDuSwiperProgram === 'function') {
			window.initDuSwiperProgram();
		}
	}

	function normalizeSelectOptions(scope) {
		(scope || document).querySelectorAll('.du-filter-native select').forEach(function (select) {
			const allOption = select.querySelector('option[value="All"], option[value="_none"], option[value=""]');

			if (allOption) {
				if (select.name.indexOf('field_universidad_target_id') !== -1) {
					allOption.textContent = 'Todas las universidades';
				}

				if (select.name.indexOf('tipo_programa_docente') !== -1) {
					allOption.textContent = 'Todos los programas';
				}
			}
		});
	}

	function initSearchInput(scope) {
		const input = (scope || document).querySelector('.du-seach__content input[name="title"]');

		if (!input) return;

		input.setAttribute('id', 'program-search');
		input.setAttribute('placeholder', 'Buscar programa...');
		input.setAttribute('autocomplete', 'off');
	}

	function syncCustomFilters(scope) {
		const root = scope || document;
		const form = root.querySelector(
			'form[data-drupal-selector="views-exposed-form-dermau-docentes-page-1"]'
		) || document.querySelector(
			'form[data-drupal-selector="views-exposed-form-dermau-docentes-page-1"]'
		);
		if (!form) return;
		root.querySelectorAll('.du-seach__content .du-filter-down[data-target]').forEach(function (filter) {
			const target = filter.getAttribute('data-target');
			const nativeSelect = form.querySelector('select[name="' + target + '"]');
			const title = filter.querySelector('.du-filter-down__title');
			if (!nativeSelect || !title) return;
			const currentValue = nativeSelect.value || 'All';
			const selectedOption = nativeSelect.querySelector('option[value="' + CSS.escape(currentValue) + '"]');
			if (selectedOption && selectedOption.textContent.trim() !== '' && selectedOption.textContent.trim() !== '- Any -') {
				title.textContent = selectedOption.textContent.trim();
				title.setAttribute('data-value', currentValue);
			} else if (target === 'field_universidad_target_id') {
				title.textContent = 'Todas las universidades';
				title.setAttribute('data-value', 'All');
			} else if (target === 'tipo_programa_docente') {
				title.textContent = 'Todos los programas';
				title.setAttribute('data-value', 'All');
			}
		});
	}



	function getProgramasForm(element) {
		if (!element) return null;
		const form = element.closest('form');
		if (!form) return null;
		if (form.matches('[data-drupal-selector="views-exposed-form-dermau-docentes-page-1"]') || form.querySelector('[data-drupal-selector="edit-submit-dermau-docentes"]')) {
			return form;
		}
		return null;
	}

	function bindDocumentEvents() {
		if (document.body.dataset.programasFilterBound === 'true') {
			return;
		}
		document.body.dataset.programasFilterBound = 'true';
		document.addEventListener('click', function (e) {
			const header = e.target.closest('.du-seach__content .du-filter-down[data-target] .du-filter-down__header');
			if (header) {
				e.preventDefault();
				e.stopPropagation();
				const currentFilter = header.closest('.du-filter-down');
				if (!currentFilter) return;
				document.querySelectorAll('.du-seach__content .du-filter-down[data-target]').forEach(function (filter) {
					if (filter !== currentFilter) {
						filter.classList.remove('active');
						filter.classList.remove('open');
					}
				});
				const willOpen = !currentFilter.classList.contains('active');
				currentFilter.classList.toggle('active', willOpen);
				currentFilter.classList.toggle('open', willOpen);
				return;
			}
			const item = e.target.closest('.du-seach__content .du-filter-down[data-target] .du-filter-down__options li');
			if (item) {
				e.preventDefault();
				e.stopPropagation();
				const filter = item.closest('.du-filter-down');
				const form = getProgramasForm(item);
				if (!filter || !form) return;
				const target = filter.getAttribute('data-target');
				const nativeSelect = form.querySelector('select[name="' + target + '"]');
				const title = filter.querySelector('.du-filter-down__title');
				if (!nativeSelect || !title) return;
				const rawValue = item.getAttribute('data-value');
				const value = rawValue === null || rawValue === '' ? 'All' : rawValue;
				const text = item.textContent.trim();
				if (nativeSelect.value === value && title.textContent.trim() === text) {
					filter.classList.remove('active');
					filter.classList.remove('open');
					return;
				}
				nativeSelect.value = value;
				nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
				title.textContent = text;
				title.setAttribute('data-value', value);
				filter.classList.remove('active');
				filter.classList.remove('open');
				triggerDrupalAjax(form);
				return;
			}
			if (!e.target.closest('.du-seach__content .du-filter-down[data-target]')) {
				closeAllProgramFilters(document);
			}
		});

		document.addEventListener('input', function (e) {
			const input = e.target.closest('.du-seach__content input[name="title"]');
			if (!input) return;
			const form = getProgramasForm(input);
			if (!form) return;
			clearTimeout(input._duSearchTimeout);
			input._duSearchTimeout = setTimeout(function () { triggerDrupalAjax(form); }, 500);
		});

		document.addEventListener('keydown', function (e) {
			const input = e.target.closest('.du-seach__content input[name="title"]');
			if (!input) return;
			if (e.key === 'Enter') {
				e.preventDefault();
				clearTimeout(input._duSearchTimeout);
				const form = getProgramasForm(input);
				if (!form) return;
				triggerDrupalAjax(form);
			}
		});

		if (window.jQuery) {
			window.jQuery(document).ajaxComplete(function () {
				normalizeSelectOptions(document);
				initSearchInput(document);
				syncCustomFilters(document);
				closeAllProgramFilters(document);
				refreshProgramSwiper();
			});
		}
	}

	function applyCountryFilter(value) {
		const view = document.querySelector(
			'[data-docentes-active-tab]'
		);

		if (view) {
			view.setAttribute(
				'data-docentes-active-tab',
				value
			);
		}
		document
			.querySelectorAll('.du-programs-item[data-tipo-conferencista]')
			.forEach(function (item) {
				const isVisible =
					item.dataset.tipoConferencista === value;

				item.style.display = isVisible ? '' : 'none';
			});

		document
			.querySelectorAll('.du-docentes-tabs__button')
			.forEach(function (tab) {
				const isActive =
					tab.dataset.tipoConferencista === value;

				tab.classList.toggle('active', isActive);
				tab.setAttribute(
					'aria-selected',
					isActive ? 'true' : 'false'
				);
			});
	}

	function initCountryTabs(context) {
		const buttons = once(
			'docentesCountryTabs',
			'.du-docentes-tabs__button',
			context
		);

		buttons.forEach(function (button) {
			button.addEventListener('click', function () {
				const value = button.dataset.tipoConferencista;

				if (!value) {
					return;
				}

				applyCountryFilter(value);
			});
		});

		const activeButton = document.querySelector(
			'.du-docentes-tabs__button.active'
		);

		const initialValue = activeButton
			? activeButton.dataset.tipoConferencista
			: 'internacional';

		applyCountryFilter(initialValue);
	}

	function sortSearchResults() {
		const container = document.querySelector('.du-programs-container');

		if (!container) {
			return;
		}

		const cards = Array.from(
			container.querySelectorAll('.du-programs-item')
		);

		cards.sort(function (cardA, cardB) {
			const visibleEventA = cardA.querySelector(
				'.du-card__expert-conference:not([hidden])'
			);

			const visibleEventB = cardB.querySelector(
				'.du-card__expert-conference:not([hidden])'
			);

			const dateA = visibleEventA
				? visibleEventA.dataset.eventoFecha || '9999-12-31'
				: '9999-12-31';

			const dateB = visibleEventB
				? visibleEventB.dataset.eventoFecha || '9999-12-31'
				: '9999-12-31';

			return dateA.localeCompare(dateB);
		});

		cards.forEach(function (card) {
			container.appendChild(card);
		});
	}

	function applyAdvancedSearch() {
		const panel = document.querySelector(
			'[data-docentes-search-panel]'
		);

		if (!panel || panel.hidden) {
			return;
		}

		const tipo = panel.querySelector(
			'[data-docentes-filter="tipo"]'
		)?.value || '';

		const tematica = panel.querySelector(
			'[data-docentes-filter="tematica"]'
		)?.value || '';

		const sala = panel.querySelector(
			'[data-docentes-filter="sala"]'
		)?.value || '';

		const docente = panel.querySelector(
			'[data-docentes-filter="docente"]'
		)?.value || '';

		const cards = document.querySelectorAll('.du-programs-item');
		let visibleCards = 0;

		cards.forEach(function (card) {
			const docenteMatch =
				!docente || card.dataset.docenteId === docente;

			let visibleEvents = 0;

			card
				.querySelectorAll('.du-card__expert-conference')
				.forEach(function (event) {
					const tipoMatch =
						!tipo || event.dataset.eventoTipo === tipo;

					const tematicaMatch =
						!tematica || event.dataset.eventoTematica === tematica;

					const salaMatch =
						!sala || event.dataset.eventoSala === sala;

					const isVisible =
						docenteMatch
						&& tipoMatch
						&& tematicaMatch
						&& salaMatch;

					event.hidden = !isVisible;

					if (isVisible) {
						visibleEvents++;
					}
				});

			const cardIsVisible = docenteMatch && visibleEvents > 0;

			card.style.display = cardIsVisible ? 'block' : 'none';

			if (cardIsVisible) {
				visibleCards++;
			}
		});

		const emptyMessage = document.querySelector(
			'[data-docentes-search-empty]'
		);

		if (emptyMessage) {
			emptyMessage.hidden = visibleCards > 0;
		}

		sortSearchResults();
	}

	function applyDocentesView(mode) {
		const isSearch = mode === 'search';

		const searchPanel = document.querySelector(
			'[data-docentes-search-panel]'
		);

		const countryTabs = document.querySelector('.du-docentes-tabs');

		if (searchPanel) {
			searchPanel.hidden = !isSearch;
		}

		if (countryTabs) {
			countryTabs.hidden = isSearch;
		}

		document
			.querySelectorAll('[data-docentes-view]')
			.forEach(function (button) {
				const isActive = button.dataset.docentesView === mode;

				button.classList.toggle('active', isActive);
				button.setAttribute(
					'aria-selected',
					isActive ? 'true' : 'false'
				);
			});

		if (isSearch) {
			applyAdvancedSearch();
			return;
		}

		document
			.querySelectorAll('.du-card__expert-conference')
			.forEach(function (event) {
				event.hidden = false;
			});

		const activeCountry = document.querySelector(
			'.du-docentes-tabs__button.active'
		);

		applyCountryFilter(
			activeCountry
				? activeCountry.dataset.tipoConferencista
				: 'internacional'
		);
	}

	function initAdvancedSearch(context) {
		once(
			'docentesViewMode',
			'[data-docentes-view]',
			context
		).forEach(function (button) {
			button.addEventListener('click', function () {
				applyDocentesView(button.dataset.docentesView);
			});
		});

		once(
			'docentesAdvancedFilter',
			'[data-docentes-search-panel] select[data-docentes-filter]',
			context
		).forEach(function (select) {
			select.addEventListener('change', applyAdvancedSearch);
		});
	}

	Drupal.behaviors.docentesFilter = {
		attach: function (context) {
			initCountryTabs(context);
			initAdvancedSearch(context);

			once('docentesFilter', 'form[data-drupal-selector="views-exposed-form-dermau-docentes-page-1"]', context).forEach(function (form) {
				normalizeSelectOptions(form);
				initSearchInput(form);
				syncCustomFilters(form);
			});

			bindDocumentEvents();
		}
	};

})(Drupal, once);