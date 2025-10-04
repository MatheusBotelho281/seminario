// js/menu.js
document.addEventListener("DOMContentLoaded", () => {
    const menuPlaceholder = document.getElementById("menu-placeholder");
  
    if (menuPlaceholder) {
      fetch("./includes/menu.html")
        .then(response => response.text())
        .then(html => {
          menuPlaceholder.innerHTML = html;
  
          // ativa o mesmo comportamento de dropdown
          const menus = [
            { dropdown: "semi-dropdown", submenu: "submenu-seminario" },
            { dropdown: "educ-dropdown", submenu: "submenu-educandario" },
            { dropdown: "voc-dropdown", submenu: "submenu-vocacao" },
          ];
  
          menus.forEach(({ dropdown, submenu }) => {
            const dropdownEl = document.getElementById(dropdown);
            const submenuEl = document.getElementById(submenu);
            if (!dropdownEl || !submenuEl) return;
  
            submenuEl.style.display = "none";
            dropdownEl.addEventListener("mouseenter", () => {
              submenuEl.style.display = "block";
            });
            dropdownEl.addEventListener("mouseleave", () => {
              submenuEl.style.display = "none";
            });
          });
        })
        .catch(err => console.error("Erro ao carregar menu:", err));
    }
  });
  document.addEventListener("DOMContentLoaded", () => {
    const menuPlaceholder = document.getElementById("menu-placeholder");
  
    if (!menuPlaceholder) return;
  
    fetch("./includes/menu.html")
      .then((res) => res.text())
      .then((html) => {
        menuPlaceholder.innerHTML = html;
  
        // Dropdown desktop
        const menus = [
          { dropdown: "semi-dropdown", submenu: "submenu-seminario" },
          { dropdown: "educ-dropdown", submenu: "submenu-educandario" },
          { dropdown: "voc-dropdown", submenu: "submenu-vocacao" },
        ];
  
        menus.forEach(({ dropdown, submenu }) => {
          const dropdownEl = document.getElementById(dropdown);
          const submenuEl = document.getElementById(submenu);
          if (!dropdownEl || !submenuEl) return;
  
          submenuEl.style.display = "none";
          dropdownEl.addEventListener("mouseenter", () => {
            submenuEl.style.display = "block";
          });
          dropdownEl.addEventListener("mouseleave", () => {
            submenuEl.style.display = "none";
          });
        });
  
        // Menu mobile
        const menuBtn = document.getElementById("menu-btn");
        const menuMobile = document.getElementById("menu-mobile");
  
        if (menuBtn && menuMobile) {
          menuBtn.addEventListener("click", () => {
            menuMobile.classList.toggle("hidden");
          });
        }
      })
      .catch((err) => console.error("Erro ao carregar menu:", err));
  });
  
  