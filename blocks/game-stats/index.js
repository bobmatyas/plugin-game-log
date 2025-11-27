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
					el(PanelBody, { title: __('Display Options', 'mode7-game-log'), initialOpen: true },
						el(ToggleControl, {
							label: __('Show Total Games', 'mode7-game-log'),
							checked: showTotal,
							onChange: (value) => setAttributes({ showTotal: value })
						}),
						el(ToggleControl, {
							label: __('Show Played', 'mode7-game-log'),
							checked: showPlayed,
							onChange: (value) => setAttributes({ showPlayed: value })
						}),
						el(ToggleControl, {
							label: __('Show Playing', 'mode7-game-log'),
							checked: showPlaying,
							onChange: (value) => setAttributes({ showPlaying: value })
						}),
						el(ToggleControl, {
							label: __('Show Backlog', 'mode7-game-log'),
							checked: showBacklog,
							onChange: (value) => setAttributes({ showBacklog: value })
						}),
						el(ToggleControl, {
							label: __('Show Wishlist', 'mode7-game-log'),
							checked: showWishlist,
							onChange: (value) => setAttributes({ showWishlist: value })
						})
					)
				),
				el('div', { className: 'game-log-stats-block-editor' },
					el('h3', {}, __('Game Statistics', 'mode7-game-log')),
					el('p', {}, __('This block will display your game collection statistics on the frontend.', 'mode7-game-log')),
					el('div', { className: 'stats-preview' },
						showTotal && el('div', { className: 'stat-box' },
							el('h4', {}, '--'),
							el('p', {}, __('Total Games', 'mode7-game-log'))
						),
						showPlayed && el('div', { className: 'stat-box' },
							el('h4', {}, '--'),
							el('p', {}, __('Played', 'mode7-game-log'))
						),
						showPlaying && el('div', { className: 'stat-box' },
							el('h4', {}, '--'),
							el('p', {}, __('Playing', 'mode7-game-log'))
						),
						showBacklog && el('div', { className: 'stat-box' },
							el('h4', {}, '--'),
							el('p', {}, __('Backlog', 'mode7-game-log'))
						),
						showWishlist && el('div', { className: 'stat-box' },
							el('h4', {}, '--'),
							el('p', {}, __('Wishlist', 'mode7-game-log'))
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
