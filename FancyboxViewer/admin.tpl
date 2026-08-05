<div class="titrePage">
  <h2>{'Fancybox Viewer'|@translate}</h2>
</div>

<form method="post" action="{$FANCYBOX_ADMIN_ACTION}" class="properties">
  <input type="hidden" name="pwg_token" value="{$PWG_TOKEN}">

  <fieldset>
    <legend>{'General'|@translate}</legend>
    <ul>
      <li>
        <label>
          <input type="checkbox" name="enabled" value="1" {if $conf_fancybox.enabled}checked="checked"{/if}> 
          <strong>{'Enable the Fancybox Viewer plugin'|@translate}</strong>
        </label>
      </li>
      <li>
        <label><strong>{'Fancybox library source:'|@translate}</strong></label><br>
        <label><input type="radio" name="fancybox_source" value="cdn" {if $conf_fancybox.fancybox_source == 'cdn'}checked="checked"{/if}> {'Official CDN (jsDelivr - Recommended)'|@translate}</label><br>
        <label><input type="radio" name="fancybox_source" value="local" {if $conf_fancybox.fancybox_source == 'local'}checked="checked"{/if}> {'Local copy (<code>vendor/fancybox/</code>)'|@translate}</label>
      </li>
      <li>
        <label><strong>{'Image display size:'|@translate}</strong>
          <select name="image_size">
            <option value="medium" {if $conf_fancybox.image_size == 'medium'}selected="selected"{/if}>{'Medium'|@translate}</option>
            <option value="large" {if $conf_fancybox.image_size == 'large'}selected="selected"{/if}>{'Large'|@translate}</option>
            <option value="xlarge" {if $conf_fancybox.image_size == 'xlarge'}selected="selected"{/if}>{'XLarge'|@translate}</option>
            <option value="xxlarge" {if $conf_fancybox.image_size == 'xxlarge'}selected="selected"{/if}>{'XXLarge'|@translate}</option>
          </select>
        </label>
      </li>
      <li>
        <label><strong>{'Image limit for the album:'|@translate}</strong>
          <input type="number" name="max_items_limit" value="{$conf_fancybox.max_items_limit}" min="50" max="5000">
        </label>
      </li>
    </ul>
  </fieldset>

<fieldset>
    <legend>{'Locations & Carousel Scope'|@translate}</legend>
    <ul>
      <li>
        <label>
          <input type="checkbox" name="open_from_thumbnails" value="1" {if !empty($conf_fancybox.open_from_thumbnails)}checked="checked"{/if}>
          <strong>{'Open Fancybox from thumbnails'|@translate}</strong> {'(album page grid)'|@translate}
        </label>
      </li>
      <li>
        <label>
          <input type="checkbox" name="open_from_picture" value="1" {if !empty($conf_fancybox.open_from_picture)}checked="checked"{/if}>
          <strong>{'Open Fancybox from the main image'|@translate}</strong> {'(on the individual photo page)'|@translate}
        </label>
      </li>
      <li>
        <label>
          <input type="checkbox" name="load_full_album" value="1" {if !empty($conf_fancybox.load_full_album)}checked="checked"{/if}>
          <strong>{'Load the entire album via the API'|@translate}</strong> {'(if unchecked, only navigates among the photos of the current page)'|@translate}
        </label>
      </li>
    </ul>
  </fieldset>

  <fieldset>
    <legend>{'Captions & Titles'|@translate}</legend>
    <ul>
      <li><label><input type="checkbox" name="show_caption" value="1" {if $conf_fancybox.show_caption}checked="checked"{/if}> {'Show the photo title'|@translate}</label></li>
      <li><label><input type="checkbox" name="hide_auto_names" value="1" {if $conf_fancybox.hide_auto_names}checked="checked"{/if}> {'Automatically hide generated names (IMG_..., PXL_..., etc.)'|@translate}</label></li>
    </ul>
  </fieldset>

  <fieldset>
    <legend>{'Buttons & Slideshow'|@translate}</legend>
    <ul>
      <li><label><input type="checkbox" name="page_link" value="1" {if $conf_fancybox.page_link}checked="checked"{/if}> {'Show the button to the Piwigo photo page'|@translate}</label></li>
      <li><label><input type="checkbox" name="open_new_tab" value="1" {if $conf_fancybox.open_new_tab}checked="checked"{/if}> {'Open the photo page in a new tab'|@translate}</label></li>
      <li><label><input type="checkbox" name="enable_download" value="1" {if $conf_fancybox.enable_download}checked="checked"{/if}> {'Original image download button'|@translate}</label></li>
      <li><label><input type="checkbox" name="enable_zoom" value="1" {if $conf_fancybox.enable_zoom}checked="checked"{/if}> {'Zoom button'|@translate}</label></li>
      <li><label><input type="checkbox" name="enable_fullscreen" value="1" {if $conf_fancybox.enable_fullscreen}checked="checked"{/if}> {'Fullscreen button'|@translate}</label></li>
<li><label><input type="checkbox" name="show_thumb_button" value="1" {if $conf_fancybox.show_thumb_button}checked="checked"{/if}> {'Show the thumbnails button'|@translate}</label></li>
	<li><label><input type="checkbox" name="enable_slideshow" value="1" {if $conf_fancybox.enable_slideshow}checked="checked"{/if}> {'Enable the Slideshow'|@translate}</label></li>
      <li><label><input type="checkbox" name="infinite" value="1" {if $conf_fancybox.infinite}checked="checked"{/if}> {'Infinite loop navigation'|@translate}</label></li>
<li>
    <label>
        {'Slideshow interval (ms)'|@translate}
        <input type="number"
               name="slideshow_timeout"
               min="500"
               step="100"
               value="{$conf_fancybox.slideshow_timeout}">
    </label>
</li>
    </ul>
  </fieldset>

  <fieldset>
    <legend>{'Album Restriction'|@translate}</legend>
    <ul>
      <li>
        <label><strong>{'Filter mode:'|@translate}</strong>
          <select name="filter_mode">
            <option value="all" {if $conf_fancybox.filter_mode == 'all'}selected="selected"{/if}>{'Active on all albums'|@translate}</option>
            <option value="include" {if $conf_fancybox.filter_mode == 'include'}selected="selected"{/if}>{'Active ONLY on the selected albums'|@translate}</option>
            <option value="exclude" {if $conf_fancybox.filter_mode == 'exclude'}selected="selected"{/if}>{'Active everywhere EXCEPT on the selected albums'|@translate}</option>
          </select>
        </label>
      </li>
      {if !empty($categories)}
      <li>
        <label><strong>{'Albums concerned:'|@translate}</strong></label><br>
        <select name="categories[]" multiple="multiple" size="6" style="min-width:250px;">
          {foreach from=$categories item=cat}
            <option value="{$cat.id}" {if in_array($cat.id, $conf_fancybox.album_categories)}selected="selected"{/if}>{$cat.name}</option>
          {/foreach}
        </select>
      </li>
      {/if}
    </ul>
  </fieldset>

  <p class="formAction">
    <input class="submit" type="submit" name="submit" value="{'Save changes'|@translate}">
  </p>
</form>
