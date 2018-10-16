<?php

namespace MPHB\Views;

class ViewActions {

	public function __construct(){
		$this->init();
	}

	public function init(){
		$this->initLoopRoomActions();
		$this->initLoopServiceActions();
		$this->initSingleRoomTypeActions();
		$this->initSingleServiceActions();
		$this->initChekoutActions();
	}

	private function initLoopRoomActions(){

		/* 	Attributes	 */
		add_action( 'mphb_render_loop_room_type_before_attributes', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesTitle' ), 10 );
		add_action( 'mphb_render_loop_room_type_before_attributes', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesListOpen' ), 20 );

		if ( MPHB()->settings()->main()->isAdultsAllowed() ) {
			add_action( 'mphb_render_loop_room_type_attributes', array( '\MPHB\Views\LoopRoomTypeView',
				'renderAdults' ), 10 );
		}
		if ( MPHB()->settings()->main()->isChildrenAllowed() ) {
			add_action( 'mphb_render_loop_room_type_attributes', array( '\MPHB\Views\LoopRoomTypeView',
				'renderChildren' ), 20 );
		}
		add_action( 'mphb_render_loop_room_type_attributes', array( '\MPHB\Views\LoopRoomTypeView',
			'renderFacilities' ), 30 );
		add_action( 'mphb_render_loop_room_type_attributes', array( '\MPHB\Views\LoopRoomTypeView',
			'renderView' ), 40 );
		add_action( 'mphb_render_loop_room_type_attributes', array( '\MPHB\Views\LoopRoomTypeView',
			'renderSize' ), 50 );
		add_action( 'mphb_render_loop_room_type_attributes', array( '\MPHB\Views\LoopRoomTypeView',
			'renderBedType' ), 60 );
		add_action( 'mphb_render_loop_room_type_attributes', array( '\MPHB\Views\LoopRoomTypeView',
			'renderCategories' ), 70 );
		add_action( 'mphb_render_loop_room_type_attributes', array( '\MPHB\Views\LoopRoomTypeView',
			'renderCustomAttributes' ), 80 );

		add_action( 'mphb_render_loop_room_type_after_attributes', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesListClose' ), 10 );

		/* 	Attributes - Categories	 */
		add_action( 'mphb_render_loop_room_type_before_categories', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderCategoriesListItemOpen' ), 10 );
		add_action( 'mphb_render_loop_room_type_before_categories', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderCategoriesTitle' ), 20 );
		add_action( 'mphb_render_loop_room_type_before_categories', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesListItemValueHolderOpen' ), 30 );

		add_action( 'mphb_render_loop_room_type_after_categories', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesListItemValueHolderClose' ), 10 );
		add_action( 'mphb_render_loop_room_type_after_categories', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderCategoriesListItemClose' ), 20 );

		/* 	Attributes - Facilities	 */
		add_action( 'mphb_render_loop_room_type_before_facilities', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderFacilitiesListItemOpen' ), 10 );
		add_action( 'mphb_render_loop_room_type_before_facilities', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderFacilitiesTitle' ), 20 );
		add_action( 'mphb_render_loop_room_type_before_facilities', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesListItemValueHolderOpen' ), 30 );

		add_action( 'mphb_render_loop_room_type_after_facilities', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesListItemValueHolderClose' ), 10 );
		add_action( 'mphb_render_loop_room_type_after_facilities', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderFacilitiesListItemClose' ), 20 );

		/* 	Attributes - Custom Attributes	 */
		add_action( 'mphb_render_loop_room_type_before_custom_attribute', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderCustomAttributesListItemOpen' ), 10, 1 );
		add_action( 'mphb_render_loop_room_type_before_custom_attribute', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderCustomAttributesTitle' ), 20, 1 );
		add_action( 'mphb_render_loop_room_type_before_custom_attribute', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesListItemValueHolderOpen' ), 30 );

		add_action( 'mphb_render_loop_room_type_after_custom_attribute', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesListItemValueHolderClose' ), 10 );
		add_action( 'mphb_render_loop_room_type_after_custom_attribute', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderCustomAttributesListItemClose' ), 20 );

		/* 	Attributes - View	 */
		add_action( 'mphb_render_loop_room_type_before_view', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderViewListItemOpen' ), 10 );
		add_action( 'mphb_render_loop_room_type_before_view', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderViewTitle' ), 20 );
		add_action( 'mphb_render_loop_room_type_before_view', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesListItemValueHolderOpen' ), 30 );

		add_action( 'mphb_render_loop_room_type_after_view', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesListItemValueHolderClose' ), 10 );
		add_action( 'mphb_render_loop_room_type_after_view', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderViewListItemClose' ), 20 );

		/* 	Attributes - Size	 */
		add_action( 'mphb_render_loop_room_type_before_size', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderSizeListItemOpen' ), 10 );
		add_action( 'mphb_render_loop_room_type_before_size', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderSizeTitle' ), 20 );
		add_action( 'mphb_render_loop_room_type_before_size', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesListItemValueHolderOpen' ), 30 );

		add_action( 'mphb_render_loop_room_type_after_size', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesListItemValueHolderClose' ), 10 );
		add_action( 'mphb_render_loop_room_type_after_size', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderSizeListItemClose' ), 20 );

		/* 	Attributes - Bed Type	 */
		add_action( 'mphb_render_loop_room_type_before_bed_type', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderBedTypeListItemOpen' ), 10 );
		add_action( 'mphb_render_loop_room_type_before_bed_type', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderBedTypeTitle' ), 20 );
		add_action( 'mphb_render_loop_room_type_before_bed_type', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesListItemValueHolderOpen' ), 30 );

		add_action( 'mphb_render_loop_room_type_after_bed_type', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesListItemValueHolderClose' ), 10 );
		add_action( 'mphb_render_loop_room_type_after_bed_type', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderBedTypeListItemClose' ), 20 );

		/* 	Attributes - Adults		 */
		add_action( 'mphb_render_loop_room_type_before_adults', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAdultsListItemOpen' ), 10 );
		add_action( 'mphb_render_loop_room_type_before_adults', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAdultsTitle' ), 20 );
		add_action( 'mphb_render_loop_room_type_before_adults', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesListItemValueHolderOpen' ), 30 );

		add_action( 'mphb_render_loop_room_type_after_adults', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesListItemValueHolderClose' ), 10 );
		add_action( 'mphb_render_loop_room_type_after_adults', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAdultsListItemClose' ), 20 );

		/* 	Attributes - Children	 */
		add_action( 'mphb_render_loop_room_type_before_children', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderChildrenListItemOpen' ), 10 );
		add_action( 'mphb_render_loop_room_type_before_children', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderChildrenTitle' ), 20 );
		add_action( 'mphb_render_loop_room_type_before_children', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesListItemValueHolderOpen' ), 30 );

		add_action( 'mphb_render_loop_room_type_after_children', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderAttributesListItemValueHolderClose' ), 10 );
		add_action( 'mphb_render_loop_room_type_after_children', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderChildrenListItemClose' ), 20 );

		/* 	Featured Image	 */
		add_action( 'mphb_render_loop_room_type_before_featured_image', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderFeaturedImageParagraphOpen' ), 10 );

		add_action( 'mphb_render_loop_room_type_after_featured_image', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderFeaturedImageParagraphClose' ), 10 );

		/* 	Gallery		 */
		add_action( 'mphb_render_loop_room_type_after_gallery', array( '\MPHB\Views\LoopRoomTypeView',
			'_enqueueGalleryScripts' ), 10 );
		add_action( 'mphb_render_loop_room_type_before_gallery', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderImagesWrapperOpen' ), 10 );
		add_action( 'mphb_render_loop_room_type_after_gallery', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderImagesWrapperClose' ), 20 );

		/* 	Price	 */
		add_action( 'mphb_render_loop_room_type_before_price', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderPriceParagraphOpen' ), 10 );
		add_action( 'mphb_render_loop_room_type_before_price', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderPriceTitle' ), 20 );

		add_action( 'mphb_render_loop_room_type_after_price', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderPriceParagraphClose' ), 10 );

		/* 	Title	 */
		add_action( 'mphb_render_loop_room_type_before_title', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderTitleHeadingOpen' ), 10 );

		add_action( 'mphb_render_loop_room_type_after_title', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderTitleHeadingClose' ), 10 );

		/* 	Book Button	 */
		add_action( 'mphb_render_loop_room_type_before_book_button', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderBookButtonWrapperOpen' ), 10 );
		add_action( 'mphb_render_loop_room_type_after_book_button', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderBookButtonBr' ), 10 );
		add_action( 'mphb_render_loop_room_type_after_book_button', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderBookButtonWrapperClose' ), 20 );

		/* 	View Details Button	 */
		add_action( 'mphb_render_loop_room_type_before_view_details_button', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderViewDetailsButtonParagraphOpen' ), 10 );

		add_action( 'mphb_render_loop_room_type_after_view_details_button', array( '\MPHB\Views\LoopRoomTypeView',
			'_renderViewDetailsButtonParagraphClose' ), 10 );
	}

	private function initLoopServiceActions(){
		add_action( 'mphb_render_loop_service_before_featured_image', array( '\MPHB\Views\LoopServiceView',
			'_renderFeaturedImageParagraphOpen' ), 10 );

		add_action( 'mphb_render_loop_service_after_featured_image', array( '\MPHB\Views\LoopServiceView',
			'_renderFeaturedImageParagraphClose' ), 10 );

		add_action( 'mphb_render_loop_service_before_price', array( '\MPHB\Views\LoopServiceView',
			'_renderPriceParagraphOpen' ), 10 );
		add_action( 'mphb_render_loop_service_before_price', array( '\MPHB\Views\LoopServiceView',
			'_renderPriceTitle' ), 20 );

		add_action( 'mphb_render_loop_service_after_price', array( '\MPHB\Views\LoopServiceView',
			'_renderPriceParagraphClose' ), 10 );

		add_action( 'mphb_render_loop_service_before_title', array( '\MPHB\Views\LoopServiceView',
			'_renderTitleHeadingOpen' ), 10 );
		add_action( 'mphb_render_loop_service_after_title', array( '\MPHB\Views\LoopServiceView',
			'_renderTitleHeadingClose' ), 10 );
	}

	private function initSingleRoomTypeActions(){

		/* 	Wrapper		 */
		add_action( 'mphb_render_single_room_type_wrapper_start', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderPageWrapperStart' ), 10 );
		add_action( 'mphb_render_single_room_type_wrapper_end', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderPageWrapperEnd' ), 10 );

		/* 	Content	 */
		add_action( 'mphb_render_single_room_type_content', array( '\MPHB\Views\SingleRoomTypeView',
			'renderTitle' ), 10 );
		add_action( 'mphb_render_single_room_type_content', array( '\MPHB\Views\SingleRoomTypeView',
			'renderFeaturedImage' ), 20 );
		add_action( 'mphb_render_single_room_type_content', array( '\MPHB\Views\SingleRoomTypeView',
			'renderDescription' ), 30 );
		add_action( 'mphb_render_single_room_type_content', array( '\MPHB\Views\SingleRoomTypeView',
			'renderDefaultOrForDatesPrice' ), 40 );
		add_action( 'mphb_render_single_room_type_content', array( '\MPHB\Views\SingleRoomTypeView',
			'renderAttributes' ), 50 );
		add_action( 'mphb_render_single_room_type_content', array( '\MPHB\Views\SingleRoomTypeView',
			'renderCalendar' ), 60 );
		add_action( 'mphb_render_single_room_type_content', array( '\MPHB\Views\SingleRoomTypeView',
			'renderReservationForm' ), 70 );

		/* 	Metas	 */
		add_action( 'mphb_render_single_room_type_metas', array( '\MPHB\Views\SingleRoomTypeView',
			'renderGallery' ), 10 );
		add_action( 'mphb_render_single_room_type_metas', array( '\MPHB\Views\SingleRoomTypeView',
			'renderAttributes' ), 20 );
		add_action( 'mphb_render_single_room_type_metas', array( '\MPHB\Views\SingleRoomTypeView',
			'renderDefaultOrForDatesPrice' ), 30 );
		add_action( 'mphb_render_single_room_type_metas', array( '\MPHB\Views\SingleRoomTypeView',
			'renderCalendar' ), 40 );
		add_action( 'mphb_render_single_room_type_metas', array( '\MPHB\Views\SingleRoomTypeView',
			'renderReservationForm' ), 50 );

		/* 	Attributes	 */
		add_action( 'mphb_render_single_room_type_before_attributes', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesTitle' ), 10 );
		add_action( 'mphb_render_single_room_type_before_attributes', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesListOpen' ), 20 );

		if ( MPHB()->settings()->main()->isAdultsAllowed() ) {
			add_action( 'mphb_render_single_room_type_attributes', array( '\MPHB\Views\SingleRoomTypeView',
				'renderAdults' ), 10 );
		}
		if ( MPHB()->settings()->main()->isChildrenAllowed() ) {
			add_action( 'mphb_render_single_room_type_attributes', array( '\MPHB\Views\SingleRoomTypeView',
				'renderChildren' ), 20 );
		}
		add_action( 'mphb_render_single_room_type_attributes', array( '\MPHB\Views\SingleRoomTypeView',
			'renderFacilities' ), 30 );
		add_action( 'mphb_render_single_room_type_attributes', array( '\MPHB\Views\SingleRoomTypeView',
			'renderView' ), 40 );
		add_action( 'mphb_render_single_room_type_attributes', array( '\MPHB\Views\SingleRoomTypeView',
			'renderSize' ), 50 );
		add_action( 'mphb_render_single_room_type_attributes', array( '\MPHB\Views\SingleRoomTypeView',
			'renderBedType' ), 60 );
		add_action( 'mphb_render_single_room_type_attributes', array( '\MPHB\Views\SingleRoomTypeView',
			'renderCategories' ), 70 );
		add_action( 'mphb_render_single_room_type_attributes', array( '\MPHB\Views\SingleRoomTypeView',
			'renderCustomAttributes' ), 80 );

		add_action( 'mphb_render_single_room_type_after_attributes', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesListClose' ), 10 );

		/* 	Attributes - Categories	 */
		add_action( 'mphb_render_single_room_type_before_categories', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderCategoriesListItemOpen' ), 10 );
		add_action( 'mphb_render_single_room_type_before_categories', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderCategoriesTitle' ), 20 );
		add_action( 'mphb_render_single_room_type_before_categories', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesListItemValueHolderOpen' ), 30 );

		add_action( 'mphb_render_single_room_type_after_categories', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesListItemValueHolderClose' ), 10 );
		add_action( 'mphb_render_single_room_type_after_categories', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderCategoriesListItemClose' ), 20 );

		/* 	Attributes - Facilities	 */
		add_action( 'mphb_render_single_room_type_before_facilities', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderFacilitiesListItemOpen' ), 10 );
		add_action( 'mphb_render_single_room_type_before_facilities', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderFacilitiesTitle' ), 20 );
		add_action( 'mphb_render_single_room_type_before_facilities', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesListItemValueHolderOpen' ), 30 );

		add_action( 'mphb_render_single_room_type_after_facilities', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesListItemValueHolderClose' ), 10 );
		add_action( 'mphb_render_single_room_type_after_facilities', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderFacilitiesListItemClose' ), 20 );

		/* 	Attributes - Custom Attributes	 */
		add_action( 'mphb_render_single_room_type_before_custom_attribute', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderCustomAttributesListItemOpen' ), 10, 1 );
		add_action( 'mphb_render_single_room_type_before_custom_attribute', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderCustomAttributesTitle' ), 20, 1 );
		add_action( 'mphb_render_single_room_type_before_custom_attribute', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesListItemValueHolderOpen' ), 30 );

		add_action( 'mphb_render_single_room_type_after_custom_attribute', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesListItemValueHolderClose' ), 10 );
		add_action( 'mphb_render_single_room_type_after_custom_attribute', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderCustomAttributesListItemClose' ), 20 );

		/* 	Attributes - View	 */
		add_action( 'mphb_render_single_room_type_before_view', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderViewListItemOpen' ), 10 );
		add_action( 'mphb_render_single_room_type_before_view', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderViewTitle' ), 20 );
		add_action( 'mphb_render_single_room_type_before_view', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesListItemValueHolderOpen' ), 30 );

		add_action( 'mphb_render_single_room_type_after_view', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesListItemValueHolderClose' ), 10 );
		add_action( 'mphb_render_single_room_type_after_view', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderViewListItemClose' ), 20 );

		/* 	Attributes - Size	 */
		add_action( 'mphb_render_single_room_type_before_size', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderSizeListItemOpen' ), 10 );
		add_action( 'mphb_render_single_room_type_before_size', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderSizeTitle' ), 20 );
		add_action( 'mphb_render_single_room_type_before_size', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesListItemValueHolderOpen' ), 30 );

		add_action( 'mphb_render_single_room_type_after_size', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesListItemValueHolderClose' ), 10 );
		add_action( 'mphb_render_single_room_type_after_size', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderSizeListItemClose' ), 20 );

		/* 	Attributes - Bed Type	 */
		add_action( 'mphb_render_single_room_type_before_bed_type', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderBedTypeListItemOpen' ), 10 );
		add_action( 'mphb_render_single_room_type_before_bed_type', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderBedTypeTitle' ), 20 );
		add_action( 'mphb_render_single_room_type_before_bed_type', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesListItemValueHolderOpen' ), 30 );

		add_action( 'mphb_render_single_room_type_after_bed_type', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesListItemValueHolderClose' ), 10 );
		add_action( 'mphb_render_single_room_type_after_bed_type', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderBedTypeListItemClose' ), 20 );

		/* 	Attributes - Adults		 */
		add_action( 'mphb_render_single_room_type_before_adults', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAdultsListItemOpen' ), 10 );
		add_action( 'mphb_render_single_room_type_before_adults', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAdultsTitle' ), 20 );
		add_action( 'mphb_render_single_room_type_before_adults', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesListItemValueHolderOpen' ), 30 );

		add_action( 'mphb_render_single_room_type_after_adults', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesListItemValueHolderClose' ), 10 );
		add_action( 'mphb_render_single_room_type_after_adults', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAdultsListItemClose' ), 20 );

		/* 	Attributes - Children	 */
		add_action( 'mphb_render_single_room_type_before_children', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderChildrenListItemOpen' ), 10 );
		add_action( 'mphb_render_single_room_type_before_children', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderChildrenTitle' ), 20 );
		add_action( 'mphb_render_single_room_type_before_children', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesListItemValueHolderOpen' ), 30 );

		add_action( 'mphb_render_single_room_type_after_children', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderAttributesListItemValueHolderClose' ), 10 );
		add_action( 'mphb_render_single_room_type_after_children', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderChildrenListItemClose' ), 20 );

		/* 	Calendar	 */
		add_action( 'mphb_render_single_room_type_before_calendar', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderCalendarTitle' ), 10 );

		/* 	Featured Image	 */
		add_action( 'mphb_render_single_room_type_before_featured_image', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderFeaturedImageParagraphOpen' ), 10 );

		add_action( 'mphb_render_single_room_type_after_featured_image', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderFeaturedImageParagraphClose' ), 10 );
		add_action( 'mphb_render_single_room_type_after_featured_image', array( '\MPHB\Views\SingleRoomTypeView',
			'renderGallery' ), 20 );

		/* 	Gallery		 */
		add_action( 'mphb_render_single_room_type_after_gallery', array( '\MPHB\Views\SingleRoomTypeView',
			'_enqueueGalleryScripts' ), 10 );

		/* 	Price	 */
		add_action( 'mphb_render_single_room_type_before_price', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderPriceParagraphOpen' ), 10 );
		add_action( 'mphb_render_single_room_type_before_price', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderPriceTitle' ), 20 );

		add_action( 'mphb_render_single_room_type_after_price', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderPriceParagraphClose' ), 10 );

		/* 	Title	 */
		add_action( 'mphb_render_single_room_type_before_title', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderTitleHeadingOpen' ), 10 );

		add_action( 'mphb_render_single_room_type_after_title', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderTitleHeadingClose' ), 10 );

		/* 	Reservation Form	 */
		add_action( 'mphb_render_single_room_type_before_reservation_form', array( '\MPHB\Views\SingleRoomTypeView',
			'_renderReservationFormTitle' ), 10 );
	}

	private function initSingleServiceActions(){
		add_action( 'mphb_render_single_service_before_price', array( '\MPHB\Views\SingleServiceView',
			'_renderPriceTitle' ), 10 );
		add_action( 'mphb_render_single_service_before_price', array( '\MPHB\Views\SingleServiceView',
			'_renderPriceParagraphOpen' ), 20 );

		add_action( 'mphb_render_single_service_after_price', array( '\MPHB\Views\SingleServiceView',
			'_renderPriceParagraphClose' ), 10 );
	}

	private function initChekoutActions(){
		add_action( 'mphb_sc_checkout_coupon_before_label', array( '\MPHB\Views\Shortcodes\CheckoutView', '_renderCouponCodeParagraphOpen' ) );
		add_action( 'mphb_sc_checkout_coupon_after_input', array( '\MPHB\Views\Shortcodes\CheckoutView', '_renderCouponCodeParagraphClose' ) );
		add_action( 'mphb_sc_checkout_coupon_before_button', array( '\MPHB\Views\Shortcodes\CheckoutView', '_renderCouponButtonParagraphOpen' ) );
		add_action( 'mphb_sc_checkout_coupon_after_button', array( '\MPHB\Views\Shortcodes\CheckoutView', '_renderCouponButtonParagraphClose' ) );
	}

}
