<?php
/**
 * Front-end duplicate ID audit (admin-only, query param).
 *
 * Visit: https://www.midsouthafp.org/?audit_ids=1 (must be logged in as admin).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inject duplicate-ID detection script in the footer when requested.
 */
function midsouthafp_child_audit_duplicate_ids_script() {
	if ( is_admin() || ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( empty( $_GET['audit_ids'] ) || '1' !== (string) $_GET['audit_ids'] ) {
		return;
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inline script is static; no user input.
	echo <<<'HTML'
<script>
(function() {
  const allIds = document.querySelectorAll('[id]');
  const seen = {};
  const dupes = [];
  allIds.forEach(el => {
    const id = el.id;
    if (seen[id]) {
      dupes.push({
        id: id,
        tag: el.tagName,
        classes: el.className,
        html: el.outerHTML.substring(0, 120)
      });
    } else {
      seen[id] = true;
    }
  });
  if (dupes.length > 0) {
    console.warn('DUPLICATE IDs FOUND:', dupes.length);
    console.table(dupes);
    const box = document.createElement('div');
    box.style.cssText = 'position:fixed;bottom:20px;right:20px;background:#fff;' +
      'border:2px solid red;padding:12px 16px;font-size:13px;z-index:99999;' +
      'max-width:360px;border-radius:6px;box-shadow:0 2px 12px rgba(0,0,0,.15)';
    box.innerHTML = '<strong style="color:red">' + dupes.length +
      ' duplicate IDs found</strong><br>Check browser console for details.';
    document.body.appendChild(box);
  } else {
    console.log('No duplicate IDs found on this page.');
  }
})();
</script>
HTML;
}
add_action( 'wp_footer', 'midsouthafp_child_audit_duplicate_ids_script', 999 );
