(function( $ ) {
	$( function() {
		MPHB.BookingsCalendar = can.Control.extend( {}, {
	filtersForm: null,
	customPeriodWrapper: null,
	btnPeriodPrev: null,
	btnPeriodNext: null,
	periodEl: null,
	init: function( el, args ) {
		this.filtersForm = this.element.find( '#mphb-bookings-calendar-filters' );
		this.customPeriodWrapper = this.filtersForm.find( '.mphb-custom-period-wrapper' );
		this.btnPeriodPrev = this.filtersForm.find( '.mphb-period-prev' );
		this.btnPeriodNext = this.filtersForm.find( '.mphb-period-next' );
		this.periodEl = this.filtersForm.find( '#mphb-bookings-calendar-filter-period' );
		this.searchDateFromEl = this.filtersForm.find( '.mphb-search-date-from' );
		this.searchDateToEl = this.filtersForm.find( '.mphb-search-date-to' );
		this.initDatepickers();
	},
	initDatepickers: function() {
		var datepickers = this.filtersForm.find( '.mphb-datepick' );
		datepickers.datepick( {
			dateFormat: MPHB.Plugin.myThis.data.settings.dateFormat,
			showSpeed: 0,
			showOtherMonths: true,
			monthsToShow: MPHB.Plugin.myThis.data.settings.numberOfMonthDatepicker,
			pickerClass: MPHB.Plugin.myThis.data.settings.datepickerClass
		} );
	},
	'#mphb-bookings-calendar-filter-period change': function( el, e ) {
		var period = $( el ).val();
		if ( period === 'custom' ) {
			this.customPeriodWrapper.removeClass( 'mphb-hide' );
			this.btnPeriodNext.addClass( 'mphb-hide' );
			this.btnPeriodPrev.addClass( 'mphb-hide' );
		} else {
			this.customPeriodWrapper.addClass( 'mphb-hide' );
			this.btnPeriodNext.removeClass( 'mphb-hide' );
			this.btnPeriodPrev.removeClass( 'mphb-hide' );
		}
	},
	'#mphb-booking-calendar-search-room-availability-status change': function( el, e ) {
		var status = $( el ).val();
		if ( status === '' ) {
			this.searchDateFromEl.addClass( 'mphb-hide' );
			this.searchDateToEl.addClass( 'mphb-hide' );
		} else {
			this.searchDateFromEl.removeClass( 'mphb-hide' );
			this.searchDateToEl.removeClass( 'mphb-hide' );
		}
	}

} );
/**
 * @see MPHB.format_price() in public/mphb.js
 */
MPHB.format_price = function( price, atts ) {
	atts = atts || {};

	var defaultAtts = MPHB.Plugin.myThis.data.settings.currency;
	atts = $.extend( { 'trim_zeros': false }, defaultAtts, atts );

	price = MPHB.number_format( price, atts['decimals'], atts['decimal_separator'], atts['thousand_separator'] );
	var formattedPrice = atts['price_format'].replace( '%s', price );

	if ( atts['trim_zeros'] ) {
		var regex = new RegExp('\\' + atts['decimal_separator'] + '0+$|(\\' + atts['decimal_separator'] + '\\d*[1-9])0+$');
		formattedPrice = formattedPrice.replace( regex, '$1' );
	}

	var priceHtml = '<span class="mphb-price">' + formattedPrice + '</span>';

	return priceHtml;
};

MPHB.format_percentage = function( price, atts ) {
	atts = atts || {};

	var defaultAtts = MPHB.Plugin.myThis.data.settings.currency;
	atts = $.extend( {}, defaultAtts, atts );

	price = MPHB.number_format( price, atts['decimals'], atts['decimal_separator'], atts['thousand_separator'] );
	var formattedPrice = price + '%';
	var priceHtml = '<span class="mphb-percentage">' + formattedPrice + '</span>';

	return priceHtml;
};

/**
 * @see MPHB.number_format() in public/mphb.js
 */
MPHB.number_format = function( number, decimals, dec_point, thousands_sep ) {
	// + Original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
	// + Improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +   Bugfix by: Michael White (http://crestidg.com)
	var sign = '', i, j, kw, kd, km;

	// Input sanitation & defaults
	decimals = decimals || 0
	dec_point = dec_point || '.'
	thousands_sep = thousands_sep || ','

	if ( number < 0 ) {
		sign = '-';
		number *= -1;
	}

	i = parseInt( number = ( +number || 0 ).toFixed( decimals ) ) + '';

	if ( ( j = i.length ) > 3 ) {
		j = j % 3;
	} else {
		j = 0;
	}

	km = ( j ? i.substr( 0, j ) + thousands_sep : '' );
	kw = i.substr( j ).replace( /(\d{3})(?=\d)/g, '$1' + thousands_sep );
	kd = ( decimals ? dec_point + Math.abs( number - i ).toFixed( decimals ).replace( /-/, 0 ).slice( 2 ) : '' );

	return sign + km + kw + kd;
};

MPHB.Plugin = can.Construct.extend( {
	myThis: null
}, {
	data: null,
	init: function( el, args ) {
		MPHB.Plugin.myThis = this;
		this.data = MPHB._data;
		delete MPHB._data;
		var ctrls = $( '.mphb-ctrl:not([data-inited])' );
		this.setControls( ctrls );
	},
	getVersion: function() {
		return this.data.version;
	},
	getPrefix: function() {
		return this.data.prefix;
	},
	addPrefix: function( str, separator ) {
		separator = (typeof separator !== 'undefined') ? separator : '-';
		return this.getPrefix() + separator + str;
	},
	setControls: function( ctrls ) {
		var ctrl, type;
		$.each( ctrls, function() {
			type = $( this ).attr( 'data-type' );
			switch ( type ) {
				case 'text':
					break;
				case 'number':
					ctrl = new MPHB.NumberCtrl( $( this ) );
					break;
				case 'total-price':
					ctrl = new MPHB.TotalPriceCtrl( $( this ) );
					break;
				case 'price-breakdown':
					ctrl = new MPHB.PriceBreakdownCtrl( $( this ) );
					break;
				case 'gallery':
					ctrl = new MPHB.GalleryCtrl( $( this ) );
					break;
				case 'datepicker':
					ctrl = new MPHB.DatePickerCtrl( $( this ) );
					break;
				case 'color-picker':
					ctrl = new MPHB.ColorPickerCtrl( $( this ) );
					break;
				case 'complex':
					ctrl = new MPHB.ComplexCtrl( $( this ) );
					break;
				case 'complex-vertical':
					ctrl = new MPHB.ComplexVerticalCtrl( $( this ) );
					break;
				case 'dynamic-select':
					ctrl = new MPHB.DynamicSelectCtrl( $( this ) );
					break;
				case 'multiple-checkbox':
					ctrl = new MPHB.MultipleCheckboxCtrl( $( this ) );
					break;
				case 'amount':
					ctrl = new MPHB.AmountCtrl( $( this ) );
					break;
				case 'rules-list':
					ctrl = new MPHB.RulesListCtrl( $( this ) );
					break;
				case 'variable-pricing':
					ctrl = new MPHB.VariablePricingCtrl( $( this ) );
					break;
			}
			$( this ).attr( 'data-inited', true );
		} );
	}
} );

// table.wp-list-table
MPHB.AttributesCustomOrder = can.Control.extend(
	{},
	{
		ITEM_SELECTOR: 'tbody tr:not(.inline-edit-row)',

		TERM_ID_SELECTOR: '.check-column input',
		TERM_ID_CLASS: '.check-column',

		COLUMN_HANDLE: '<td class="column-handle"></td>',
		COLUMN_HANDLE_CLASS: '.column-handle',

		/**
		 * @param {jQuery} element table.wp-list-table
		 * @param {Array} args
		 */
		init: function( element, args ) {
			this._super( element, args );

			// Add sortable handle to each item
			element.find( 'tr:not(.inline-edit-row)' ).append( this.COLUMN_HANDLE );
			element.find( this.COLUMN_HANDLE_CLASS ).show();

			$( document ).ajaxComplete( this.onAjaxComplete.bind( this ) );

			element.sortable( {
				items: this.ITEM_SELECTOR,
				cursor: 'move',
				handle: this.COLUMN_HANDLE_CLASS,
				axis: 'y',
				opacity: 0.65,
				scrollSensitivity: 40,
				update: this.onUpdate.bind( this )
			} );
		},

		onAjaxComplete: function( event, request, options ) {
			if ( request && request.readyState === 4 && request.status === 200 && options.data && ( options.data.indexOf( '_inline_edit' ) >= 0 || options.data.indexOf( 'add-tag' ) >= 0 ) ) {
				this.addMissingSortHandles();
				$( document.body ).trigger( 'init_tooltips' );
			}
		},

		onUpdate: function( event, ui ) {
			var termId	   = ui.item.find( this.TERM_ID_SELECTOR ).val(); // This post ID
			var nextTermId = ui.item.next().find( this.TERM_ID_SELECTOR ).val();

			// Show spinner
			ui.item.find( this.TERM_ID_SELECTOR ).hide();
			ui.item.find( this.TERM_ID_CLASS ).append( '<span class="mphb-preloader"></span>' );

			var self = this;

			$.ajax( {
				url: MPHB.Plugin.myThis.data.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'mphb_attributes_custom_ordering',
					mphb_nonce: MPHB.Plugin.myThis.data.nonces.mphb_attributes_custom_ordering,
					term_id: termId,
					next_term_id: nextTermId,
					taxonomy_name: MPHB.Plugin.myThis.data.settings.editTaxonomyName
				},
				complete: function( jqXHR, textStatus ) {
					ui.item.find( self.TERM_ID_CLASS ).find( '.mphb-preloader' ).remove();
					ui.item.find( self.TERM_ID_SELECTOR ).show();
				}
			} );

			// Fix cell colors
			this.element.find( 'tbody tr' ).each( function( index, element ) {
				if ( index % 2 == 0 ) {
					$( this ).addClass( 'alternate' );
				} else {
					$( this ).removeClass( 'alternate' );
				}
			} );
		},

		addMissingSortHandles: function() {
			var allRows	   = this.element.find( 'tbody > tr' );
			var handleRows = this.element.find( 'tbody > tr > td' + this.COLUMN_HANDLE_CLASS ).parent();

			if ( allRows.length == handleRows.length ) {
				return;
			}

			var self = this;

			allRows.each( function( index, element ) {
				if ( !handleRows.is( element ) ) {
					$( element ).append( self.COLUMN_HANDLE );
				}
			} );

			this.element.find( this.COLUMN_HANDLE_CLASS ).show();
		}
	}
);

MPHB.WPGallery = can.Construct.extend( {
	myThis: null,
	getInstance: function() {
		if ( MPHB.WPGallery.myThis === null ) {
			MPHB.WPGallery.myThis = new MPHB.WPGallery();
		}
		return MPHB.WPGallery.myThis;
	}
},
{
	frame: null,
	ctrl: null,
	init: function() {
		var self = this;
		MPHB.WPGallery.myThis = this;
		Attachment = wp.media.model.Attachment;

		wp.media.controller.MPHBGallery = wp.media.controller.FeaturedImage.extend( {
			defaults: parent._.defaults( {
				id: 'mphb-media-library-gallery',
				title: MPHB.Plugin.myThis.data.translations.roomTypeGalleryTitle,
				toolbar: 'main-insert',
				filterable: 'uploaded',
				library: wp.media.query( {type: 'image'} ),
				multiple: 'add',
				editable: true,
				priority: 60,
				syncSelection: false
			}, wp.media.controller.Library.prototype.defaults ),
			updateSelection: function() {
				var selection = this.get( 'selection' ),
					ids = MPHB.WPGallery.myThis.ctrl.get(),
					attachments;
				if ( '' !== ids && -1 !== ids ) {
					attachments = parent._.map( ids.split( /,/ ), function( id ) {
						return Attachment.get( id );
					} );
				}
				selection.reset( attachments );
			}
		} );

		wp.media.view.MediaFrame.MPHBGallery = wp.media.view.MediaFrame.Post.extend( {
			// Define insert - MPHB state
			createStates: function() {
				var options = this.options;

				// Add the default states
				this.states.add( [
					// Main states
					new wp.media.controller.MPHBGallery()
				] );
			},
			// Removing let menu from manager
			bindHandlers: function() {
				wp.media.view.MediaFrame.Select.prototype.bindHandlers.apply( this, arguments );
				this.on( 'toolbar:create:main-insert', this.createToolbar, this );

				var handlers = {
					content: {
						'embed': 'embedContent',
						'edit-selection': 'editSelectionContent'
					},
					toolbar: {
						'main-insert': 'mainInsertToolbar'
					}
				};

				parent._.each( handlers, function( regionHandlers, region ) {
					parent._.each( regionHandlers, function( callback, handler ) {
						this.on( region + ':render:' + handler, this[ callback ], this );
					}, this );
				}, this );
			},
			// Changing main button title
			mainInsertToolbar: function( view ) {
				var controller = this;

				this.selectionStatusToolbar( view );

				view.set( 'insert', {
					style: 'primary',
					priority: 80,
					text: MPHB.Plugin.myThis.data.translations.addGalleryToRoomType,
					requires: {selection: true},
					click: function() {
						var state = controller.state(),
							selection = state.get( 'selection' );

						controller.close();
						state.trigger( 'insert', selection ).reset();
					}
				} );
			}
		} );

		this.frame = new wp.media.view.MediaFrame.MPHBGallery( parent._.defaults( {}, {
			state: 'mphb-media-library-gallery',
			library: {type: 'image'},
			multiple: true
		} ) );

		this.frame.on( 'open', this.proxy( 'onOpen' ) );
		this.frame.on( 'insert', this.proxy( 'setImage' ) );
	},
	open: function( ctrl ) {
		this.ctrl = ctrl;
		this.frame.open();
	},
	onOpen: function() {
		var frame = this.frame;
		frame.reset();
		var ids = this.ctrl.getArray();
		if ( ids.length ) {
			var attachment = null;
			ids.forEach( function( id ) {
				attachment = wp.media.attachment( id );
				attachment.fetch();
				frame.state().get( 'selection' ).add( attachment );
			} );
		}
	},
	setImage: function() {
		var ids = [ ];
		var models = this.frame.state().get( 'selection' ).models;
		$.each( models, function( key, model ) {
			var attributes = model.attributes;
			ids.push( attributes.id );
		} );
		this.ctrl.set( ids.join( ',' ) );
	}
} );
MPHB.Ctrl = can.Control.extend( {
	renderValue: function( control ) {
		var type = control.attr( 'data-type' );
		return control.find( 'input[type="' + type + '"]' ).val();
	}
}, {
	parentForm: null,
	init: function( el, args ) {
		this.parentForm = this.element.closest( 'form' );
	}
} );
/**
 *
 * @requires ./ctrl.js
 */
MPHB.AmountCtrl = MPHB.Ctrl.extend( {
	renderValue: function( control ) {
		var inputs = control.find( 'input[type="number"]:not(:disabled)' );
		var renderType = control.children( '.mphb-amount-inputs' ).attr( 'data-render-type' );
		var formatFunction = renderType == 'price' ? MPHB.format_price : MPHB.format_percentage;

		if ( inputs.length == 1 ) {
			return formatFunction( inputs.val() );
		} else {
			var result = MPHB.Plugin.myThis.data.translations.adults;
			result += formatFunction( $( inputs[0] ).val() );
			result += '<br />' + MPHB.Plugin.myThis.data.translations.children;
			result += formatFunction( $( inputs[1] ).val() );
			return result;
		}
	}
}, {
	mainWrapper: null,
	singleInputGroup: null,
	multipleInputsGroup: null,
	commonAmountInput: null,
	adultsAmountInput: null,
	childrenAmountInput: null,

	dependencyCtrl: null,
	singleInputTriggers: [],
	multipleInputsTriggers: [],

	init: function( element, args ) {
		this._super( element, args );

		this.mainWrapper = this.element.children( '.mphb-amount-inputs' );
		this.singleInputGroup = this.mainWrapper.children( '.mphb-amount-single-input-group' );
		this.multipleInputsGroup = this.mainWrapper.children( '.mphb-amount-multiple-inputs-group' );
		this.commonAmountInput = this.singleInputGroup.find( 'input.mphb-amount-common-input' );
		this.adultsAmountInput = this.multipleInputsGroup.find( 'input.mphb-amount-adults-input' );
		this.childrenAmountInput = this.multipleInputsGroup.find( 'input.mphb-amount-children-input' );

		// Init dependency control
		var dependencyName = this.mainWrapper.attr( 'data-dependency' );
		if ( dependencyName ) {
			var self = this;
			this.dependencyCtrl = this.element.closest( 'form' ).find( '[name="' + dependencyName + '"]' );
			this.dependencyCtrl.on( 'change', function( event ) {
				var value = $( this ).val();
				self.onTrigger( value );
			} );
		}

		// Init triggers and flags
		this.singleInputTriggers = this.mainWrapper.attr( 'data-single-triggers' ).split( ',' );
		this.multipleInputsTriggers = this.mainWrapper.attr( 'data-multiple-triggers' ).split( ',' );
	},
	onTrigger: function( value ) {
		var switchToSingle = this.singleInputTriggers.indexOf( value ) != -1;
		var switchToMultiple = this.multipleInputsTriggers.indexOf( value ) != -1;

		if ( value.indexOf( 'percent' ) != -1 ) {
			this.mainWrapper.attr( 'data-render-type', 'percentage' );
		} else {
			this.mainWrapper.attr( 'data-render-type', 'price' );
		}

		if ( switchToSingle ) {
			this.switchToSingleInput();
		} else if ( switchToMultiple ) {
			this.switchToMultipleInputs();
		}
	},
	switchToSingleInput: function() {
		this.adultsAmountInput.prop( 'disabled', true );
		this.childrenAmountInput.prop( 'disabled', true );
		this.commonAmountInput.prop( 'disabled', false );
		this.multipleInputsGroup.addClass( 'mphb-hide' );
		this.singleInputGroup.removeClass( 'mphb-hide' );
	},
	switchToMultipleInputs: function() {
		this.commonAmountInput.prop( 'disabled', true );
		this.adultsAmountInput.prop( 'disabled', false );
		this.childrenAmountInput.prop( 'disabled', false );
		this.singleInputGroup.addClass( 'mphb-hide' );
		this.multipleInputsGroup.removeClass( 'mphb-hide' );
	}
} );

/**
 *
 * @requires ./ctrl.js
 */
MPHB.ColorPickerCtrl = MPHB.Ctrl.extend( {}, {
	input: null,
	init: function( el, args ) {

		this._super( el, args );

		this.input = this.element.find( 'input' )

		this.input.spectrum( {
			allowEmpty: true,
			preferredFormat: "hex",
			showInput: true,
			showInitial: true,
			showAlpha: false
		} );

	}

} );
/**
 *
 * @requires ./ctrl.js
 */
MPHB.ComplexCtrl = MPHB.Ctrl.extend( {}, {
	prototypeItem: null,
	itemsHolder: null,
	lastIndex: null,
	uniqid: null,
	itemSelector: 'tr',
	metaName: null,
	init: function( el, args ) {
		this._super( el, args );
		this.uniqid = this.element.children( 'table' ).attr( 'data-uniqid' );
		this.metaName = this.element.children( 'input[type="hidden"]:first-of-type' ).attr( 'name' );
		this.initItemsHolder();
		this.initAddBtn();
		this.initDeleteBtns();
		this.preparePrototypeItem();
		this.initLastIndex();
		this.setKeys( this.itemsHolder.children( this.itemSelector ) );
	},
	makeItemsHolderSortable: function() {
        if ( this.itemsHolder.parent().hasClass( 'mphb-separate-sortable-table' ) ) {
            this.itemsHolder.sortable( {
                handle: '.mphb-sortable-handle',
                cursor: 'move'
            } );
        } else {
            this.itemsHolder.sortable();
        }
	},
	initLastIndex: function() {
		this.lastIndex = 0;
		var self = this;
		this.itemsHolder.children( this.itemSelector ).each( function( index, item ){
			self.lastIndex = Math.max( self.lastIndex, parseInt( $( item ).attr( 'data-id' ) ) );
		} );
	},
	initItemsHolder: function() {
		this.itemsHolder = this.element.children( 'table' ).children( 'tbody' );
		if ( this.itemsHolder.hasClass( 'mphb-sortable' ) ) {
			this.makeItemsHolderSortable();
		}
	},
	initAddBtn: function() {
		var self = this;
		this.element.on( 'click', '.mphb-complex-add-item[data-id="' + this.uniqid + '"]', function( e ) {
			e.preventDefault();
			self.addItem();
		} )
	},
	initDeleteBtns: function() {
		var self = this;
		this.itemsHolder.on( 'click', '.mphb-complex-delete-item[data-id="' + this.uniqid + '"]', function( e ) {
			e.preventDefault();
			self.deleteItem( $( this ).closest( self.itemSelector ) );
		} );
	},
	preparePrototypeItem: function() {
		var item = this.itemsHolder.children( '.mphb-complex-item-prototype' );
		this.prototypeItem = item.clone();
		this.prototypeItem.removeClass( 'mphb-hide mphb-complex-item-prototype' ).find( '[name]:not(.mphb-keep-disabled)' ).each( function() {
			$( this ).removeAttr( 'disabled' );
		} );

		item.remove();
	},
	getIncIndex: function() {
		return ++this.lastIndex;
	},
	setKeys: function( wrappers ) {
		var self = this;
		var name, id, forAttr, key, dependency, $wrapper;
		var keyRegEx = new RegExp( '%key_' + this.uniqid + '%', 'g' );
		var keyPlaceholder = '%key_' + this.uniqid + '%';
		wrappers.each( function( index, wrapper ) {
			$wrapper = $( wrapper );
			key = $wrapper.attr( 'data-id' );

			if ( key === keyPlaceholder ) {
				key = self.getIncIndex();
				$wrapper.attr( 'data-id', key );
			}
			$wrapper.find( '[name*="[%key_' + self.uniqid + '%]"]' ).each( function() {
				name = $( this ).attr( 'name' ).replace( keyRegEx, key );
				$( this ).attr( 'name', name )
				if ( $( this ).attr( 'id' ) ) {
					id = $( this ).attr( 'id' ).replace( keyRegEx, key ).replace( /\[|\]/g, '__' );
					$( this ).attr( 'name', name ).attr( 'id', id );
				}
			} );
			$wrapper.find( '[for*="[%key_' + self.uniqid + '%]"]' ).each( function() {
				forAttr = $( this ).attr( 'for' ).replace( keyRegEx, key ).replace( /\[|\]/g, '__' );
				$( this ).attr( 'for', forAttr );
			} );
			$wrapper.find( '[data-dependency*="%key_' + self.uniqid + '%"]' ).each( function() {
				dependency = $( this ).attr( 'data-dependency' ).replace( keyRegEx, key );
				$( this ).attr( 'data-dependency', dependency );
			} );
		} );
	},
	clonePrototypeItem: function() {
		var clonedItem = this.prototypeItem.clone();
		this.setKeys( clonedItem );
		return clonedItem;
	},
	addItemToHolder: function( item ) {
		this.itemsHolder.append( item );
	},
	deleteItem: function( item ) {
		item.remove();
	},
	addItem: function() {
		var item = this.clonePrototypeItem();
		this.addItemToHolder( item );
		var ctrls = item.find( '.mphb-ctrl:not([data-inited])' );
		MPHB.Plugin.myThis.setControls( ctrls );
	}

} );
/**
 *
 * @requires ./complex-ctrl.js
 */
MPHB.ComplexVerticalCtrl = MPHB.ComplexCtrl.extend( {}, {
	itemSelector: 'tbody',
	lastIndexInput: null,
	minItemsCount: 0,
	init: function( el, args ) {
		this._super( el, args );
		this.minItemsCount = this.itemsHolder.attr( 'data-min-items-count' );
	},
	initLastIndex: function() {
		this.lastIndexInput = this.itemsHolder.find( '>tfoot .mphb-complex-last-index' );
		this.lastIndex = this.lastIndexInput.val();
	},
	getIncIndex: function() {
		var index = this._super();
		this.lastIndexInput.val( index );
		return index;
	},
	initItemsHolder: function() {
		this.itemsHolder = this.element.children( 'table' );
	},
	addItemToHolder: function( item ) {
		this.itemsHolder.children( 'tfoot' ).before( item );
	},
	disableDeleteButtons: function() {
		var deleteButtons = this.itemsHolder.children( this.itemSelector ).children( '.mphb-complex-item-actions-holder' ).find( '.mphb-complex-delete-item' );
		deleteButtons.attr( 'disabled', 'disabled' ).addClass( 'mphb-hide' );
	},
	enableDeleteButtons: function() {
		var deleteButtons = this.itemsHolder.children( this.itemSelector ).children( '.mphb-complex-item-actions-holder' ).find( '.mphb-complex-delete-item' );
		deleteButtons.removeAttr( 'disabled' ).removeClass( 'mphb-hide' );
	},
	updateItemActions: function() {
		var itemCount = this.itemsHolder.children( this.itemSelector ).length;
		if ( itemCount <= this.minItemsCount ) {
			this.disableDeleteButtons();
		} else {
			this.enableDeleteButtons();
		}
	},
	updateDefaultItem: function() {
		var defaultRadio = this.itemsHolder.children( this.itemSelector ).find( '>.mphb-complex-item-actions-holder [name="' + this.metaName + '[default]"]' );
		if ( !defaultRadio.filter( ':checked' ).length ) {
			defaultRadio.first().attr( 'checked', 'checked' );
		}
	},
	deleteItem: function( item ) {
		this._super( item );
		this.updateItemActions();
		this.updateDefaultItem();
	},
	addItem: function() {
		this._super();
		this.updateItemActions();
		this.updateDefaultItem();
	}

} );

/**
 *
 * @requires ./ctrl.js
 */
MPHB.DatePickerCtrl = MPHB.Ctrl.extend( {
	renderValue: function( control ) {
		return control.find( 'input[type="text"]' ).val();
	}
}, {
	input: null,
	hiddenInput: null,
	init: function( el, args ) {
		this._super( el, args );
		this.input = this.element.find( 'input[type="text"]' );
		this.hiddenInput = this.element.find( 'input[type="hidden"]' );

		this.fixDate();

		if ( !this.input.attr( 'readonly' ) ) {
			this.input.datepick( {
				dateFormat: MPHB.Plugin.myThis.data.settings.dateFormat,
				altField: this.hiddenInput,
				altFormat: MPHB.Plugin.myThis.data.settings.dateTransferFormat,
				showSpeed: 0,
				showOtherMonths: false,
				monthsToShow: MPHB.Plugin.myThis.data.settings.numberOfMonthDatepicker,
				pickerClass: MPHB.Plugin.myThis.data.settings.datepickerClass
			} );
		}

		if ( !this.input.attr( 'required' ) ) {
			var hiddenInput = this.hiddenInput;

			this.input.on( 'change', function () {
				var input = $( this );
				var value = input.val();

				if ( value == '' ) {
					hiddenInput.val( '' );
				}
			} );
		}
	},
	/**
	 * Fix date in customer date format
	 *
	 * @returns {undefined}
	 */
	fixDate: function() {
		if ( !this.hiddenInput.val() ) {
//			this.input.val( '' );
		} else {
			var date = $.datepick.parseDate( MPHB.Plugin.myThis.data.settings.dateTransferFormat, this.hiddenInput.val() );
			var fixedValue = $.datepick.formatDate( MPHB.Plugin.myThis.data.settings.dateFormat, date );
			this.input.val( fixedValue );
		}
	}
} );
/**
 *
 * @requires ./ctrl.js
 */
MPHB.DynamicSelectCtrl = MPHB.Ctrl.extend( {}, {
	input: null,
	dependencyCtrl: null,
	ajaxAction: null,
	ajaxNonce: null,
	errorsWrapper: null,
	preloader: null,
	defaultValue: null,
	defaultOption: null,
	complexId: null,
	group: '',
	init: function( el, args ) {
		this._super( el, args );
		this.input = this.element.find( 'select' );
		this.defaultValue = this.input.attr( 'data-default' );
		this.defaultOption = this.input.find( 'option[value="' + this.defaultValue + '"]' ).clone();
		this.errorsWrapper = this.element.find( '.mphb-errors-wrapper' );
		this.preloader = this.element.find( '.mphb-preloader' );
		this.ajaxAction = this.input.attr( 'data-ajax-action' );
		this.ajaxNonce = this.input.attr( 'data-ajax-nonce' );

		this.initDependencyCtrl();
		this.initComplexId();
		this.initGroup();
	},
	initDependencyCtrl: function() {
		var dependencyName = this.input.attr( 'data-dependency' );
		this.dependencyCtrl = this.element.closest( 'form' ).find( '[name="' + dependencyName + '"]' );
		var self = this;
		this.dependencyCtrl.on( 'change', function( e ) {
			self.updateList();
		} ).on( 'focus', function( e ) {
			self.hideErrors();
		} );
	},
	initComplexId: function() {
		var complexRow = this.element.parents( 'tr[data-id]' );
		if ( complexRow.length > 0 ) {
			this.complexId = parseInt( complexRow.attr( 'data-id' ) );
		}
	},
	initGroup: function() {
		var complexWrapper = this.element.parents( '.mphb-ctrl-rules-list' );
		if ( complexWrapper.length > 0 ) {
			this.group = complexWrapper.attr( 'data-group' );
		}
	},
	setOptions: function( source ) {
		var self = this;
		this.input.html( this.defaultOption.clone() );
		$.each( source, function( value, label ) {
			self.input.append( $( '<option />', {
				'value': value,
				'html': label
			} ) );
		} );
	},
	updateList: function() {
		var self = this;
		this.hideErrors();
		this.showPreloader();
		this.input.html( this.defaultOption.clone() );
		var data = {
			action: this.ajaxAction,
			mphb_nonce: this.ajaxNonce,
			formValues: this.parseFormToJSON()
		};
		$.ajax( {
			url: MPHB.Plugin.myThis.data.ajaxUrl,
			type: 'GET',
			dataType: 'json',
			"data": data,
			success: function( response ) {
				if ( response.hasOwnProperty( 'success' ) ) {
					if ( response.success ) {
						self.setOptions( response.data.options );
					} else {
						self.showError( response.data.message );
					}
				} else {
					self.showError( MPHB.Plugin.myThis.data.translations.errorHasOccured );
				}
			},
			error: function( jqXHR ) {
				self.showError( MPHB.Plugin.myThis.data.translations.errorHasOccured );
			},
			complete: function( jqXHR ) {
				self.hidePreloader();
			}
		} );
	},
	parseFormToJSON: function() {
		var data = this.parentForm.serializeJSON();
		if ( this.group != '' ) {
			data = data[this.group];
		}
		if ( this.complexId != null ) {
			data = data[this.complexId];
		}
		return data;
	},
	showPreloader: function() {
		this.preloader.removeClass( 'mphb-hide' );
	},
	hidePreloader: function() {
		this.preloader.addClass( 'mphb-hide' );
	},
	hideErrors: function() {
		this.errorsWrapper.empty().addClass( 'mphb-hide' );
	},
	showError: function( message ) {
		this.errorsWrapper.html( message ).removeClass( 'mphb-hide' );
	}

} );
/**
 *
 * @requires ./ctrl.js
 */
MPHB.GalleryCtrl = MPHB.Ctrl.extend( {}, {
	input: null,
	previewHolder: null,
	addGalleryBtn: null,
	removeGalleryBtn: null,
	init: function( el, args ) {
		this._super( el, args );
		this.input = this.element.find( 'input[type=hidden]' );
		this.previewHolder = this.element.find( 'img' ).on( 'click', this.proxy( 'organizeGallery' ) );
		this.addGalleryBtn = this.element.find( '.mphb-admin-organize-gallery-add' ).on( 'click', this.proxy( 'organizeGallery' ) );
		this.removeGalleryBtn = this.element.find( '.mphb-admin-organize-gallery-remove' ).on( 'click', this.proxy( 'removeGallery' ) );
	},
	get: function() {
		return this.input.val();
	},
	getArray: function() {
		var value = this.get();
		return value !== '' ? this.get().split( /,/ ) : [ ];
	},
	set: function( value ) {
		this.input.val( value );
		this.updatePreview();
		this.updateBtnsVisibility();
	},
	updateBtnsVisibility: function() {
		var value = this.get();
		if ( value !== '' ) {
			this.removeGalleryBtn.removeClass( 'mphb-hide' );
			this.addGalleryBtn.addClass( 'mphb-hide' );
		} else {
			this.removeGalleryBtn.addClass( 'mphb-hide' );
			this.addGalleryBtn.removeClass( 'mphb-hide' );
		}
	},
	updatePreview: function() {
		var value = this.get();
		if ( value !== '' ) {
			var previewId = value.split( ',' ).shift();
			var attachment = wp.media.attachment( previewId );
			var previewSrc = attachment.attributes.sizes.medium.url;
			this.previewHolder.removeClass( 'mphb-hide' ).attr( 'src', previewSrc );
		} else {
			this.previewHolder.addClass( 'mphb-hide' ).attr( 'src', '' );
		}
	},
	organizeGallery: function( e ) {
		e.preventDefault();
		MPHB.WPGallery.getInstance().open( this );
	},
	removeGallery: function( e ) {
		e.preventDefault();
		this.set( '' );
	}
} );
/**
 * @requires ./ctrl.js
 */
MPHB.MultipleCheckboxCtrl = MPHB.Ctrl.extend( {
    renderValue: function( control ) {
        var inputs		 = control.find( 'input[type="checkbox"]' );
        var variantAll	 = inputs.filter( '.mphb-checkbox-all:checked' );
        var checked		 = inputs.filter( ':checked' );

        if ( variantAll.length > 0 || checked.length == inputs.length ) {
            return MPHB.Plugin.myThis.data.translations.all;
        } else {
            var labels = [];

            checked.each( function() {
                var label = $( this ).parent().text();
                labels.push( label );
            } );

            if ( labels.length > 0 ) {
                return labels.join( ', ' );
            } else {
                return MPHB.Plugin.myThis.data.translations.none;
            }
        }
    }
}, {
	/**
	 * @type {Object[]} An array of checkbox input elements. "All" element is
	 * not included (see selectAllCheckbox).
	 */
	checkboxes: null,
	/**
	 * @type {Object} "All" checkbox element (if exists).
	 */
	selectAllCheckbox: null,
	init: function( element, args ) {
		this._super( element, args );

		this.checkboxes = this.element.find( 'input[type="checkbox"]:not(.mphb-checkbox-all)' );
		this.selectAllCheckbox = this.element.find( 'input[type="checkbox"].mphb-checkbox-all' );

		// Fix for complex inputs that removes all 'disabled="disabled"'
		if ( this.selectAllCheckbox.prop( 'checked' ) ) {
			this.disableCheckboxes();
		}
	},
	".mphb-checkbox-all click": function() {
		if ( this.selectAllCheckbox.prop( 'checked' ) ) {
			this.disableCheckboxes();
			this.selectCheckboxes();
		} else {
			this.enableCheckboxes();
		}
	},
	/**
	 * @param {Object} element "Select all" button element.
	 * @param {Object} event
	 */
	".mphb-checkbox-select-all click": function( element, event ) {
		event.preventDefault();

		this.selectCheckboxes();
	},
	/**
	 * @param {Object} element "Unselect all" button element.
	 * @param {Object} event
	 */
	".mphb-checkbox-unselect-all click": function( element, event ) {
		event.preventDefault();

		this.unselectAll();
		this.enableCheckboxes();
	},
	disableCheckboxes: function() {
		this.checkboxes.prop( 'disabled', true );
	},
	enableCheckboxes: function() {
		this.checkboxes.prop( 'disabled', false );
	},
	selectCheckboxes: function() {
		this.checkboxes.prop( 'checked', true );
	},
	unselectCheckboxes: function() {
		this.checkboxes.prop( 'checked', false );
	},
	unselectAll: function() {
		this.selectAllCheckbox.prop( 'checked', false );
		this.unselectCheckboxes();
	}
} );

/**
 *
 * @requires ./ctrl.js
 */
MPHB.NumberCtrl = MPHB.Ctrl.extend( {
	renderValue: function( control ) {
		var input = control.children( 'input[type="number"]' );
		return input.val() + input.parent().text(); // value + "&nbsp;inner label"
	}
}, {
	input: null,
	disableOn: [],
	init: function( element, args ) {
		this._super( element, args );

		this.input = this.element.children( '[name]' );

		// Init dependency control
		var dependencyName = this.input.attr( 'data-dependency' );
		var disableOn = this.input.attr( 'data-disable-on' );

		if ( dependencyName && disableOn ) {
			this.disableOn = disableOn.split( ',' );

			var self = this;
			var dependencyCtrl = this.element.closest( 'form' ).find( '[name="' + dependencyName + '"]' );
			dependencyCtrl.on( 'change', function( event ) {
				var value = $( this ).val();
				self.onDependencyChange( value );
			} );
		}
	},
	onDependencyChange: function( dependencyValue ) {
		if ( this.disableOn.indexOf( dependencyValue ) != -1 ) {
			this.input.prop( 'disabled', true );
		} else {
			this.input.prop( 'disabled', false );
		}
	}
} );

/**
 *
 * @requires ./ctrl.js
 */
MPHB.PriceBreakdownCtrl = MPHB.Ctrl.extend( {}, {
	// See also assets/js/public/dev/checkout/checkout-form.js
	".mphb-price-breakdown-expand click": function( element, event ) {
		event.preventDefault();

		$( element ).blur(); // Don't save a:focus style on last clicked item

		var tr = $( element ).parents( 'tr.mphb-price-breakdown-group' );
		tr.find( '.mphb-price-breakdown-rate' ).toggleClass( 'mphb-hide' );
		tr.nextUntil( 'tr.mphb-price-breakdown-group' ).toggleClass( 'mphb-hide' );
	}
} );

/**
 * @requires ./ctrl.js
 */
MPHB.RulesListCtrl = MPHB.Ctrl.extend( {}, {
	editClass: 'mphb-rules-list-editing',
	editText: '',
	doneText: '',
	lastIndex: -1,
	rulesCount: 0,
	table: null,
	tbody: null,
	rulePrototype: null,
	editingRule: null,
	noRulesMessage: null,
	prependNewItems: false, // ... instead of append
	init: function( element, args ) {
		this._super( element, args );

		this.editText = MPHB.Plugin.myThis.data.translations.edit;
		this.doneText = MPHB.Plugin.myThis.data.translations.done;

		this.noRulesMessage = element.find( '.mphb-rules-list-empty-message' );

		this.table = element.children( 'table' );
		this.tbody = this.table.children( 'tbody' );

		if ( this.tbody.hasClass( 'mphb-sortable' ) ) {
			this.tbody.sortable();
			this.prependNewItems = true;
		}

		// Prepare rule prototype
		var prototypeElement = this.tbody.children( '.mphb-rules-list-prototype' );
		var rulePrototype = prototypeElement.clone();

		prototypeElement.remove();

		rulePrototype.removeClass( 'mphb-rules-list-prototype mphb-hide' );
		rulePrototype.find( '.mphb-ctrl:not(.mphb-keep-disabled) [name]:not(.mphb-keep-disabled)' ).each( function() {
			// Enable all controls
			$( this ).prop( 'disabled', false );
		} );

		this.rulePrototype = rulePrototype;

		// Find max (last) index
		var rules = this.tbody.children( 'tr' );
		var maxIndex = this.lastIndex; // -1

		rules.each( function() {
			var ruleIndex = parseInt( $( this ).attr( 'data-id' ) );
			maxIndex = Math.max( maxIndex, ruleIndex );
		} );

		this.lastIndex = maxIndex;
		this.rulesCount = rules.length;
	},
	".mphb-rules-list-add-button click": function() {
		this.addRule();
	},
	".mphb-rules-list-edit-button click": function( button, event ) {
		var rule = this.getRuleByButton( button );
		this.toggleEdit( rule );
	},
	".mphb-rules-list-delete-button click": function( button, event ) {
		var rule = this.getRuleByButton( button );
		this.deleteRule( rule );
	},
	addRule: function() {
		var rule = this.rulePrototype.clone();
		var nextIndex = this.nextIndex();

		// Set ID for new rule
		rule.attr( 'data-id', nextIndex );

		// Change ID in all names
		rule.find( '[name*="[$index$]"]' ).each( function() {
			var element = $( this );

			var name = element.attr( 'name' );
			name = name.replace( '$index$', nextIndex );
			element.attr( 'name', name );

			var id = element.attr( 'id' );
			if ( id ) {
				id = id.replace( '$index$', nextIndex );
				element.attr( 'id', id );
			}
		} );

		// Change ID in dependencies
		rule.find( '[data-dependency*="[$index$]"]' ).each( function() {
			var element = $( this );

			var dependency = element.attr( 'data-dependency' );
			dependency = dependency.replace( '$index$', nextIndex );

			element.attr( 'data-dependency', dependency );
		} );

		if ( this.prependNewItems ) {
			this.tbody.prepend( rule );
		} else {
			this.tbody.append( rule );
		}

		this.increaseRulesCount();

		// Init new controls
		var newControls = rule.find( '.mphb-ctrl:not([data-inited])' );
		MPHB.Plugin.myThis.setControls( newControls );

		this.toggleEdit( rule );
	},
	/**
	 * @param {Object} rule &lt;tr&gt; element.
	 */
	toggleEdit: function( rule ) {
		if ( this.editingRule != null ) {
			// Toggle with active editing rule means maximize new rule or
			// minimize the current one. In both cases current rule will be
			// minimized
			this.renderValues( this.editingRule );
			this.editingRule.removeClass( this.editClass );

			// Change text from "Done" to "Edit"
			this.editingRule.find( '.mphb-rules-list-edit-button' ).text( this.editText );
		}

		if ( this.isEditingRule( rule ) ) {
			this.editingRule = null; // Already removed the class
		} else {
			this.editingRule = rule;
			rule.addClass( this.editClass );

			// Change text from "Edit" to "Done"
			rule.find( '.mphb-rules-list-edit-button' ).text( this.doneText );
		}
	},
	/**
	 * @param {Object} rule &lt;tr&gt; element.
	 */
	deleteRule: function( rule ) {
		if ( this.isEditingRule( rule ) ) {
			this.editingRule = null;
		}

		rule.remove();

		this.decreaseRulesCount();
	},
	/**
	 * @param {Object} rule &lt;tr&gt; element.
	 */
	isEditingRule: function( rule ) {
		// rule.hasClass( this.editClass ) - at this time, the class can be
		// removed, see toggleEdit()
		return ( this.editingRule != null && rule[0] === this.editingRule[0] );;
	},
	/**
	 * @param {Object} button "Edit" or "Delete" button.
	 */
	getRuleByButton: function( button ) {
		return button.closest( 'tr' );
	},
	increaseRulesCount: function() {
		if ( this.rulesCount == 0 ) {
			this.noRulesMessage.addClass( 'mphb-hide' );
			this.table.removeClass( 'mphb-hide' );
		}

		this.rulesCount++;
	},
	decreaseRulesCount: function() {
		this.rulesCount--;

		if ( this.rulesCount == 0 ) {
			this.table.addClass( 'mphb-hide' );
			this.noRulesMessage.removeClass( 'mphb-hide' );
		}
	},
	nextIndex: function() {
		this.lastIndex++;
		return this.lastIndex;
	},
	/**
	 * @param {Object} rule &lt;tr&gt; element.
	 */
	renderValues: function( rule ) {
		var self = this;

		rule.children( 'td' ).each( function() {
			var td = $( this );
			var control = td.children( '.mphb-ctrl' );

			if ( control.length == 0 ) {
				return;
			}

			var result = self.renderValue( control );
			td.children( '.mphb-rules-list-rendered-value' ).html( result );
		} );
	},
	/**
	 * @param {Object} control .mphb-ctrl element.
	 *
	 * @returns {String}
	 */
	renderValue: function( control ) {
		var type = control.attr( 'data-type' );
		var result = '';

		switch ( type ) {
			case 'text':
				result = MPHB.Ctrl.renderValue( control );
				break;

			case 'datepicker':
				result = MPHB.DatePickerCtrl.renderValue( control );
				break;

			case 'textarea':
				result = control.find( 'textarea' ).val();
				break;

			case 'number':
				result = MPHB.NumberCtrl.renderValue( control );
				break;

			case 'select':
			case 'dynamic-select':
				var select = control.children( 'select' );
				var value = select.val();

				if ( value != undefined ) {
					var option = select.children( 'option[value="' + value + '"]' );
					result = option.text();
				} else {
					result = MPHB.Plugin.myThis.data.translations.none;
				}

				break;

			case 'multiple-checkbox':
				result = MPHB.MultipleCheckboxCtrl.renderValue( control );
				break;

			case 'amount':
				result = MPHB.AmountCtrl.renderValue( control );
				break;

			case 'placeholder':
				result = '-';
				break;
		}

		return result;
	}
} );

/**
 *
 * @requires ./ctrl.js
 */
MPHB.TotalPriceCtrl = MPHB.Ctrl.extend( {}, {
	preloader: null,
	input: null,
	init: function( el, args ) {
		this._super( el, args );
		this.input = this.element.find( 'input' );
		this.recalculateBtn = this.element.find( '#mphb-recalculate-total-price' );
		this.errorsWrapper = this.element.find( '.mphb-errors-wrapper' );
		this.preloader = this.element.find( '.mphb-preloader' );
	},
	set: function( value ) {
		this.input.val( value );
	},
	hideErrors: function() {
		this.errorsWrapper.empty().addClass( 'mphb-hide' );
	},
	'input focus': function() {
		this.hideErrors();
	},
	showError: function( message ) {
		this.errorsWrapper.html( message ).removeClass( 'mphb-hide' );
	},
	'#mphb-recalculate-total-price click': function( el, e ) {
		var self = this;
		this.hideErrors();
		this.showPreloader();
		var data = this.parseFormToJSON();

		$.ajax( {
			url: MPHB.Plugin.myThis.data.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'mphb_recalculate_total',
				mphb_nonce: MPHB.Plugin.myThis.data.nonces.mphb_recalculate_total,
				formValues: data
			},
			success: function( response ) {
				if ( response.hasOwnProperty( 'success' ) ) {
					if ( response.success ) {
						self.set( response.data.total );
						// Also update price breakdown
						var breakdownInput = self.element.closest( 'form' ).find( '[name="_mphb_booking_price_breakdown"]' );
						var breakdownWrapper = breakdownInput.siblings( '.mphb-price-breakdown-wrapper' );
						breakdownInput.val( response.data.price_breakdown );
						breakdownInput.prop( 'disabled', false );
						breakdownWrapper.html( response.data.price_breakdown_html );
					} else {
						self.showError( response.data.message );
					}
				} else {
					self.showError( MPHB.Plugin.myThis.data.translations.errorHasOccured );
				}
			},
			error: function( jqXHR ) {
				self.showError( MPHB.Plugin.myThis.data.translations.errorHasOccured );
			},
			complete: function( jqXHR ) {
				self.hidePreloader();
			}
		} );
	},
	showPreloader: function() {
		this.recalculateBtn.attr( 'disabled', 'disabled' );
		this.preloader.removeClass( 'mphb-hide' );
	},
	hidePreloader: function() {
		this.recalculateBtn.removeAttr( 'disabled' );
		this.preloader.addClass( 'mphb-hide' );
	},
	parseFormToJSON: function() {
		return this.parentForm.serializeJSON();
	}

} );
/**
 *
 * @requires ./ctrl.js
 */
MPHB.VariablePricingCtrl = MPHB.Ctrl.extend( {}, {
	MIN_PERIOD: 2, // See also \MPHB\Admin\Fields\VariablePricingField::MIN_PERIOD

	name: '',

	periodsTable: null, // Top table
	variationsTable: null, // Bottom table
	variationsTableBody: null,
	variationsTableFooter: null,

	afterPeriods: null, // Place for new period inputs
	afterPrices: null, // Place for new price inputs (in top table)

	pricesHeaders: null, // .mphb-pricing-price-per-night

	template: null, // Template/prototype of variation
	templateActions: null, // Place for new price inputs

	lastIndex: -1,
	lastPeriodIndex: -1,
	periodsCount: 0,

	removePeriodText: '',

	init: function( element, args ) {
		this._super( element, args );

		this.name = element.children( '.mphb-pricing-name-holder' ).attr( 'name' );

		this.removePeriodText = MPHB.Plugin.myThis.data.translations.removePeriod;

		this.periodsTable = element.children( '.mphb-pricing-periods-table' );
		this.variationsTable = element.children( '.mphb-pricing-variations-table' );
		this.variationsTableBody = this.variationsTable.children( 'tbody' );
		this.variationsTableFooter = this.variationsTable.find( 'tfoot > tr > td' );

		this.afterPeriods = this.periodsTable.find( '> tbody > tr:first-child > td:last-child' );
		this.afterPrices = this.periodsTable.find( '> tbody > tr:last-child > td:last-child' );

		this.pricesHeaders = element.find( '.mphb-pricing-price-per-night' );

		// Prepare template
		this.template = this.loadTemplate();
		this.templateActions = this.template.children( 'td:last-child' );

		// Find last/max indexes
		this.lastIndex = this.findLastIndex();
		this.lastPeriodIndex = this.findLastPeriodIndex();
		this.periodsCount = this.findPeriodsCount();

		// Watch checkbox "Enable variable pricing" to show/hide variable prices table
		this.watchCheckbox();
	},
	loadTemplate: function() {
		var templateElement = this.variationsTable.find( '.mphb-pricing-variation-template' );
		var template = templateElement.clone();

		templateElement.remove();

		template.removeClass( 'mphb-pricing-variation-prototype mphb-hide' );
		// Enable all controls
		template.find( '[name]' ).each( function() {
			$( this ).prop( 'disabled', false );
		} );

		return template;
	},
	addVariation: function () {
		var variation = this.template.clone();
		var index = this.nextIndex();

		variation.attr( 'data-index', index );

		// Change indexes in all names
		variation.find( '[name*="[$index$]"]' ).each( function() {
			var element = $( this );

			var name = element.attr( 'name' );
			name = name.replace( '$index$', index );
			element.attr( 'name', name );
		} );

		this.variationsTableBody.append( variation );
	},
	removeVariation: function( element ) {
		element.remove();
	},
	addPeriod: function() {
		var index = this.nextPeriodIndex();

		var periodInput = '<input type="number" name="' + this.name + '[periods][]" class="small-text" value="' + this.MIN_PERIOD + '" min="' + this.MIN_PERIOD + '" step="1" />';
		periodInput += '<br /><span class="dashicons dashicons-trash mphb-pricing-action mphb-pricing-remove-period" title="' + this.removePeriodText + '"></span>';
		periodInput = '<td data-period-index="' + index + '">' + periodInput + '</td>';

		var pricesAtts = '';
		var afterPrices = '';

		var priceInput = '<td data-period-index="' + index + '"><input type="text" name="' + this.name + '[prices][]" class="mphb-price-text" value=""' + pricesAtts + ' />' + afterPrices + '</td>';

		var templateInput = '<td data-period-index="' + index + '"><input type="text" name="' + this.name + '[variations][$index$][prices][]" class="mphb-price-text" value=""' + pricesAtts + ' />' + afterPrices + '</td>';

		this.afterPeriods.before( periodInput );
		this.afterPrices.before( priceInput );
		this.templateActions.before( templateInput );

		this.variationsTableBody.children( 'tr' ).each( function( index, element ) {
			var tr = $( element );
			var index = parseInt( tr.attr( 'data-index' ) );
			var afterPrices = tr.children( 'td:last-child' );
			afterPrices.before( templateInput.replace( '$index$', index ) );
		} );

		this.increasePeriodsCount();
	},
	removePeriod: function( index ) {
		this.template.find( '[data-period-index="' + index + '"]' ).remove();
		this.element.find( '[data-period-index="' + index + '"]' ).remove();
		this.decreasePeriodsCount();
	},
	nextIndex: function() {
		this.lastIndex++;
		return this.lastIndex;
	},
	nextPeriodIndex: function() {
		this.lastPeriodIndex++;
		return this.lastPeriodIndex;
	},
	findLastIndex: function() {
		var variations = this.variationsTableBody.children( 'tr' );
		var maxIndex = -1;

		variations.each( function() {
			var index = parseInt( $( this ).attr( 'data-index' ) );
			maxIndex = Math.max( maxIndex, index );
		} );

		return maxIndex;
	},
	findLastPeriodIndex: function() {
		var periods = this.periodsTable.find( '> tbody > tr:first-child > td[data-period-index]' );
		var maxIndex = -1;

		periods.each( function() {
			var index = parseInt( $( this ).attr( 'data-period-index' ) );
			maxIndex = Math.max( maxIndex, index );
		} );

		return maxIndex;
	},
	findPeriodsCount: function() {
		var periods = this.periodsTable.find( '> tbody > tr:first-child > td[data-period-index]' );
		return periods.length;
	},
	increasePeriodsCount: function() {
		this.periodsCount++;
		this.updateColspans();
	},
	decreasePeriodsCount: function() {
		this.periodsCount--;
		this.updateColspans();
	},
	updateColspans: function() {
		this.pricesHeaders.attr( 'colspan', this.periodsCount );
		this.variationsTableFooter.attr( 'colspan', this.periodsCount + 3 ); // 3 - adults, children and .mphb-pricing-remove-variation
	},
	watchCheckbox: function() {
		var self = this;
		this.element.find( '.mphb-pricing-enable-variations' ).on( 'change', function( event ) {
			self.variationsTable.toggleClass( 'mphb-hide' );
		} );
	},
	".mphb-pricing-add-variation click": function( target, event ) {
		this.addVariation();
	},
	".mphb-pricing-remove-variation click": function( target, event ) {
		var row = target.closest( 'tr' );
		this.removeVariation( row );
	},
	".mphb-pricing-add-period click": function( target, event ) {
		this.addPeriod();
	},
	".mphb-pricing-remove-period click": function( target, event ) {
		var cell = target.closest( 'td' );
		var periodIndex = cell.attr( 'data-period-index' );
		this.removePeriod( periodIndex );
	}
} );

	new MPHB.Plugin();

	$( function() {
		if ( $( '.mphb-bookings-calendar-wrapper' ) ) {
			new MPHB.BookingsCalendar( $( '.mphb-bookings-calendar-wrapper' ) );
		}

		if ( MPHB.Plugin.myThis.data.settings.isAttributesCustomOrder ) {
			new MPHB.AttributesCustomOrder( $( 'table.wp-list-table' ) );
		}
	} );
	} );
})( jQuery );