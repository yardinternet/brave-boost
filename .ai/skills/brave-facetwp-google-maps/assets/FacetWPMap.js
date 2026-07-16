const SELECTORS = {
	templateList: '.facetwp-template-list',
	templateMap: '.facetwp-template-map',
	btnList: 'js-toggle-button-list',
	btnMap: 'js-toggle-button-map',
};

const CLUSTER_COLOR = 'var(--color-primary)';
const CLUSTER_Z_BASE = 1000000;

export default () => {
	const events = () => {
		initViewToggler();
		initCustomClusterer();
	};

	const initViewToggler = () => {
		const templateList = document.querySelector( SELECTORS.templateList );
		const templateMap = document.querySelector( SELECTORS.templateMap );

		const btnList = document.getElementById( SELECTORS.btnList );
		const btnMap = document.getElementById( SELECTORS.btnMap );

		if ( ! templateList || ! templateMap || ! btnList || ! btnMap ) {
			return;
		}

		btnList.addEventListener( 'click', () => {
			if ( btnList.getAttribute( 'aria-pressed' ) === 'true' ) {
				return;
			}
			toggleView( false );
		} );

		btnMap.addEventListener( 'click', () => {
			if ( btnMap.getAttribute( 'aria-pressed' ) === 'true' ) {
				return;
			}
			toggleView( true );
		} );

		/**
		 * Toggles the visibility of templates and active state of buttons.
		 *
		 * @param {boolean} showMap - Indicates whether to show the map view.
		 */
		const toggleView = ( showMap ) => {
			templateList.classList.toggle( 'is-hidden', showMap );
			templateMap.classList.toggle( 'is-hidden', ! showMap );

			btnList.setAttribute( 'aria-pressed', String( ! showMap ) );
			btnMap.setAttribute( 'aria-pressed', String( showMap ) );
		};
	};

	const initCustomClusterer = () => {
		if ( 'object' !== typeof window.FWP || ! window.FWP.hooks ) return;

		window.FWP.hooks.addFilter(
			'facetwp_map/clusterer',
			( clusterArgs ) => ( { ...clusterArgs, renderer: clusterRenderer } )
		);
	};

	const clusterRenderer = {
		render( { count, position }, _stats, map ) {
			const icon = buildClusterIcon( count );

			return new window.google.maps.marker.AdvancedMarkerElement( {
				map,
				position,
				content: icon,
				title: `Cluster of ${ count } markers`,
				// Keep clusters above individual markers.
				zIndex: CLUSTER_Z_BASE + count,
			} );
		},
	};

	/**
	 * Builds the SVG pin element used to render a cluster marker.
	 */
	const buildClusterIcon = ( count ) => {
		const svgMarkup = `
			<svg fill="${ CLUSTER_COLOR }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240" width="55" height="55">
				<circle cx="120" cy="120" opacity=".8" r="70" />
				<circle cx="120" cy="120" opacity=".4" r="85" />
				<text x="50%" y="50%" style="fill:#fff" text-anchor="middle" font-size="60" dominant-baseline="middle" font-family="roboto,arial,sans-serif">${ count }</text>
			</svg>`;

		const parser = new DOMParser();
		const icon = parser.parseFromString(
			svgMarkup,
			'image/svg+xml'
		).documentElement;

		// Font size is scaled by the SVG viewBox, so it must be set to match.
		icon.setAttribute( 'transform', 'translate(0 28)' );
		icon.querySelector( 'text' ).style.fontSize = '60px';
		icon.querySelector( 'text' ).style.fontWeight = 'bold';
		icon.classList.add( 'facetwp-cluster-icon' );

		return icon;
	};

	events();
};
