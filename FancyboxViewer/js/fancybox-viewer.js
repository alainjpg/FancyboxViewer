document.addEventListener("DOMContentLoaded", function () {
    const rawConfig = (typeof FANCYBOX_VIEWER_DATA !== "undefined" && FANCYBOX_VIEWER_DATA.config) ? FANCYBOX_VIEWER_DATA.config : {};

    const config = {
        enable_autoplay: rawConfig.enable_slideshow !== false && rawConfig.enable_autoplay !== false,
        enable_download: rawConfig.enable_download !== false,
        enable_zoom: rawConfig.enable_zoom !== false,
        enable_fullscreen: rawConfig.enable_fullscreen !== false,
        show_thumb_button: rawConfig.show_thumb_button !== false,
        page_link: rawConfig.page_link !== false && rawConfig.show_page_link !== false,
        open_new_tab: rawConfig.open_new_tab !== false,
        open_from_thumbnails: rawConfig.open_from_thumbnails !== false,
        open_from_picture: rawConfig.open_from_picture !== false,
        load_full_album: rawConfig.load_full_album !== false
    };

    function getLargeImage(src) {
        if (!src) return "";
        if (src.includes("_data/i/")) {
            src = src.replace("_data/i/", "i.php?/");
        }
        return src.replace(/-[^-\/]+(\.[^.]+)$/i, "-xl$1");
    }

	function getOriginalImage(src) {
		if (!src) return "";
		
		// 1. Supprime proprement les routeurs virtuels de Piwigo en garantissant la présence d'un seul slash
		src = src.replace(/\/_data\/i\//i, "/");
		src = src.replace(/\/i\.php\?\//i, "/");
		
		// 2. Retire les suffixes de redimensionnement de Piwigo (ex: -sq, -th, -xl)
		return src.replace(/-[^-\/]+(\.[^.]+)$/i, "$1");
	}

    function isAutomaticFilename(text) {
        if (!text) return false;
        text = text.trim().replace(/\.[^.]+$/, "");

        if (/^(IMG|DSC|CIMG|PICT|PXL|GOPR|DJI|MVI|VID|SAM|MVIMG|OLYMP|PAN|PANA|FUJI|NIKON|SONY)([ _-]?[A-Z0-9]+)*$/i.test(text)) return true;
        if (/^PXL[ _-]?\d{8}[ _-]?\d{6}.*$/i.test(text)) return true;
        if (/^Resized[ _-]?\d{8}[ _-]?\d{6}(\(\d+\))?$/i.test(text)) return true;
        if (/^IMG[ _-]?\d{8}[ _-]?WA\d+$/i.test(text)) return true;
        if (/^Screenshot.*$/i.test(text)) return true;
        if (/^[\d\s()_-]+$/.test(text)) return true;
        if (/^Capture d[’']écran.*$/i.test(text)) return true;
        if (/^\d{8,}[ _-][a-f0-9]{6,}$/i.test(text)) return true;
        if (/^[\d _-]+$/.test(text)) return true;

        return false;
    }
	function buildFancyboxItems() {

		return FANCYBOX_VIEWER_DATA.items.map(item => {

			let caption = item.name || item.comment || "";
			if (isAutomaticFilename(caption)) caption = "";

			// Embedded Videos
			if (item.video_type) {

				let src = item.video_url;

				switch (item.video_type) {

					case "youtube":
						src = "https://www.youtube-nocookie.com/embed/" +
							  item.video_id +
							  "?autoplay=1";
						break;

					case "vimeo":
						src = "https://player.vimeo.com/video/" +
							  item.video_id;
						break;

					case "dailymotion":
						src = "https://www.dailymotion.com/embed/video/" +
							  item.video_id;
						break;
				}

				return {
					id: item.id,
					thumbSrc: item.src,
					src: src,
					type: "iframe",
					caption: caption,
					pageUrl: item.page_url
				};
			}

			// Vidéos HTML5 (VideoJS)
			if (/\.(mp4|webm|ogg)$/i.test(item.file)) {
				return {
					id: item.id,
					thumbSrc: item.src,
					src: item.download_src,
					type: "html5video",
					caption: caption,
					downloadSrc: item.download_src,
					pageUrl: item.page_url
				};
			}

			// Images
			return {
				src: item.src,
				caption: caption,
				downloadSrc: item.download_src,
				pageUrl: item.page_url
			};
		});

	}
    const toolbarRight = [];
    if (config.enable_autoplay) toolbarRight.push("autoplay");
    if (config.enable_zoom) { 
        toolbarRight.push("zoomIn"); 
        toolbarRight.push("zoomOut"); 
        toolbarRight.push("toggle1to1");
    }
    if (config.show_thumb_button) toolbarRight.push("thumbs");
    if (config.enable_download) toolbarRight.push("download");
    if (config.page_link) toolbarRight.push("pageLink");
    if (config.enable_fullscreen) toolbarRight.push("fullscreen");
    toolbarRight.push("close");

    const thumbnailLinks = document.querySelectorAll("#thumbnails a, .thumbnails a");

    if (config.open_from_thumbnails && thumbnailLinks.length > 0) {
        thumbnailLinks.forEach((a, index) => {
            a.addEventListener("click", function (e) {
                e.preventDefault();

                if (
                    config.load_full_album &&
                    typeof FANCYBOX_VIEWER_DATA !== "undefined" &&
                    FANCYBOX_VIEWER_DATA.items &&
                    FANCYBOX_VIEWER_DATA.items.length > 0
                ) {
                    const items = buildFancyboxItems();

                    const clickedImg = a.querySelector("img");
                    const clickedSrc = clickedImg ? (clickedImg.dataset.src || clickedImg.src) : "";
                    let startIndex = index;

                    if (clickedSrc) {
                        const filename = clickedSrc.split('/').pop().split('-')[0];
                        const foundIdx = items.findIndex(item => item.src.includes(filename));
                        if (foundIdx !== -1) startIndex = foundIdx;
                    }

                    launchFancybox(items, startIndex);
                } else {
                    launchLocalFancybox(index);
                }
            });
        });
    }

    const pictureImage = document.getElementById("theMainImage");

    if (config.open_from_picture && pictureImage) {
        pictureImage.style.cursor = "zoom-in";

        pictureImage.addEventListener("click", function (e) {
            e.preventDefault();

            if (!config.load_full_album) {
                let caption = pictureImage.alt || "";

                if (isAutomaticFilename(caption)) {
                    caption = "";
                }

                launchFancybox([{
                    src: getLargeImage(pictureImage.src),
                    caption: caption,
                    downloadSrc: getOriginalImage(pictureImage.src),
                    pageUrl: window.location.href
                }], 0);

                return;
            }

            if (
                typeof FANCYBOX_VIEWER_DATA === "undefined" ||
                !FANCYBOX_VIEWER_DATA.items ||
                !FANCYBOX_VIEWER_DATA.items.length
            ) {
                return;
            }

            const items = buildFancyboxItems();

			let startIndex = items.findIndex(
				item => item.id === FANCYBOX_VIEWER_DATA.current_image_id
			);

			if (startIndex === -1) {
				startIndex = 0;
			}
            if (startIndex < 0) {
                startIndex = 0;
            }

            launchFancybox(items, startIndex);
        });
    }

    function launchFancybox(items, startIndex) {
        if (typeof Fancybox === "undefined") return;

        const timeoutVal = parseInt(rawConfig.slideshow_timeout || 3000, 10);
        Fancybox.show(items, {
            startIndex: startIndex,
            animated: true,
            dragToClose: true,
            Carousel: {
			    
				Autoplay: {
					autoStart: false,
					timeout: timeoutVal
				},

				Toolbar: {
                    display: {
                        left: ["counter"],
                        middle: [],
                        right: toolbarRight
                    },
                    items: {
                        pageLink: {
                            tpl: `<button class="f-button" title="${(typeof FANCYBOX_VIEWER_DATA !== "undefined" && FANCYBOX_VIEWER_DATA.lang && FANCYBOX_VIEWER_DATA.lang.page_link) ? FANCYBOX_VIEWER_DATA.lang.page_link : "Ouvrir la page de la photo"}" type="button">
                                <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" style="vertical-align: middle;">
                                    <path d="M14 5h5v5M19 5l-8 8M19 13v5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>`,
                            click: function (toolbar) {
                                const instance = toolbar.instance || (typeof Fancybox !== "undefined" ? Fancybox.getInstance() : null);
                                if (!instance) return;
                                
                                const slide = instance.getSlide();
                                const url = slide?.pageUrl || slide?.data?.pageUrl || slide?.opts?.pageUrl;

                                if (url) {
                                    if (config.open_new_tab) {
                                        window.open(url, "_blank");
                                    } else {
                                        window.location.href = url;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        });
    }

    function launchLocalFancybox(startIndex) {
        const localItems = Array.from(thumbnailLinks).map(a => {
            const img = a.querySelector("img");
            if (!img) return null;
            const thumb = img.dataset.src || img.currentSrc || img.src;
            let caption = (img.alt || "").trim();
            if (isAutomaticFilename(caption)) caption = "";

            return {
                src: getLargeImage(thumb),
                caption: caption,
                downloadSrc: getOriginalImage(thumb),
                pageUrl: a.href
            };
        }).filter(Boolean);

        launchFancybox(localItems, startIndex);
    }
});