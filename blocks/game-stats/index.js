(function() {
	'use strict';

	// Use WordPress globals instead of ES6 imports
	const { registerBlockType } = wp.blocks;
	const { createElement: el, Fragment } = wp.element;
	const { InspectorControls } = wp.blockEditor;
	const { PanelBody, ToggleControl } = wp.components;
	const { __ } = wp.i18n;

	// Register the block
	registerBlockType('game-log/game-stats', {
		edit: function(props) {
			const { attributes, setAttributes } = props;
			const { showTotal, showPlayed, showPlaying, showBacklog, showWishlist } = attributes;

			return el(Fragment, {},
				el(InspectorControls, {},
					el(PanelBody, { title: __('Display Options', 'game-log'), initialOpen: true },
						el(ToggleControl, {
							label: __('Show Total Games', 'game-log'),
							checked: showTotal,
							onChange: (value) => setAttributes({ showTotal: value })
						}),
						el(ToggleControl, {
							label: __('Show Played', 'game-log'),
							checked: showPlayed,
							onChange: (value) => setAttributes({ showPlayed: value })
						}),
						el(ToggleControl, {
							label: __('Show Playing', 'game-log'),
							checked: showPlaying,
							onChange: (value) => setAttributes({ showPlaying: value })
						}),
						el(ToggleControl, {
							label: __('Show Backlog', 'game-log'),
							checked: showBacklog,
							onChange: (value) => setAttributes({ showBacklog: value })
						}),
						el(ToggleControl, {
							label: __('Show Wishlist', 'game-log'),
							checked: showWishlist,
							onChange: (value) => setAttributes({ showWishlist: value })
						})
					)
				),
				el('div', { className: 'game-log-stats-block-editor' },
					el('h3', {}, __('Game Statistics', 'game-log')),
					el('p', {}, __('This block will display your game collection statistics on the frontend.', 'game-log')),
					el('div', { className: 'stats-preview' },
						showTotal && el('div', { className: 'stat-box' },
							el('h4', {}, '--'),
							el('p', {}, __('Total Games', 'game-log'))
						),
						showPlayed && el('div', { className: 'stat-box' },
							el('h4', {}, '--'),
							el('p', {}, __('Played', 'game-log'))
						),
						showPlaying && el('div', { className: 'stat-box' },
							el('h4', {}, '--'),
							el('p', {}, __('Playing', 'game-log'))
						),
						showBacklog && el('div', { className: 'stat-box' },
							el('h4', {}, '--'),
							el('p', {}, __('Backlog', 'game-log'))
						),
						showWishlist && el('div', { className: 'stat-box' },
							el('h4', {}, '--'),
							el('p', {}, __('Wishlist', 'game-log'))
						)
					)
				)
			);
		},

		save: function() {
			// Server-side rendering
			return null;
		}
	});
})();
