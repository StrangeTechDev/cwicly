// shorthand no-conflict safe document-ready function
jQuery(function ($) {
  console.log("editor-enhancements.js loaded");

  const pagePath = window.location.pathname.toString();
  const pageQueryString = window.location.search.toString();
  const params = new URLSearchParams(pageQueryString);
  let savedCwiclyEditorBar;

  function onEditorHeaderChildListChange(records, observer) {
    for (const record of records) {
      for (const addedNode of record.addedNodes) {
        if (addedNode.className.includes("editor-header__center")) {
          if (savedCwiclyEditorBar) {
            addedNode.appendChild(savedCwiclyEditorBar);
          }
        }
        console.log(`Added: ${addedNode.textContent}`);
      }
      for (const removedNode of record.removedNodes) {
        if (removedNode.className.includes("editor-header__center")) {
          savedCwiclyEditorBar = removedNode.querySelector(".cc-previewer");
        }
        console.log(`Removed: ${removedNode.textContent}`);
      }
    }
  }

  if (
    (pagePath.includes("/wp-admin/post.php") &&
      params.get("action") == "edit") || 
    pagePath.includes("/wp-admin/post-new.php") || 
    (pagePath.includes("/wp-admin/site-editor.php") &&
      params.get("canvas") == "edit")
  ) {
    //alert("success!: " + pagePath + ", query: " + pageQueryString);
    //document.addEventListener("DOMContentLoaded", function() {
    console.log("DOM fully loaded and parsed");
    let timer = setInterval(function () {
      const siteHeader = document.querySelector(".editor-header");
      if (siteHeader != null) {
        clearInterval(timer);
        timer = null;

        const observerOptions = {
          childList: true,
          subtree: true,
        };

        const observer = new MutationObserver(onEditorHeaderChildListChange);
        observer.observe(siteHeader, observerOptions);
      }
    }, 3000);
    //});
  }
});
