/******/ (() => { // webpackBootstrap
/*!******************************!*\
  !*** ./src/rmu-news/view.js ***!
  \******************************/
// file: src/rmu-news/view.js
// โหลดเสร็จสมบูรณ์ก่อนเริ่มการทำงานของ script
document.addEventListener("DOMContentLoaded", function () {
  // ค้นหา container ทั้งหมดที่มี class rmu-news-shortcode
  const containers = document.querySelectorAll(".rmu-news-shortcode:not([data-initialized])");
  if (containers.length === 0) {
    console.log("RMU News: ไม่พบ container");
    return;
  }

  // ประมวลผลแต่ละ container
  containers.forEach(function (container) {
    initRmuNews(container);
  });
  function initRmuNews(container) {
    container.setAttribute("data-initialized", "true");
    const buttonsData = [{
      label: "ทั้งหมด",
      category: "ทั้งหมด"
    }, {
      label: "ข่าวสมัครงาน",
      category: "สมัครงาน"
    }, {
      label: "ข่าวประกวดราคา",
      category: "ประกวดราคา"
    }, {
      label: "ข่าวประชาสัมพันธ์",
      category: "ประชาสัมพันธ์"
    }, {
      label: "ข่าวบริการ",
      category: "บริการ"
    }, {
      label: "ข่าวพระวุณ",
      category: "สวัสดิการ"
    }, {
      label: "ข่าวสรรหา",
      category: "สรรหา"
    }];

    // ดึงการตั้งค่าจาก WordPress
    const settings = window.rmuNewsSettings || {
      apiUrl: "https://www.rmu.ac.th/api/posts/filter",
      buttonColor: "#2874fc",
      buttonTextColor: "#e0ecff",
      borderColor: "#ccc",
      buttonHoverColor: "#e0ecff",
      buttonHoverTextColor: "#2874fc",
      // เพิ่มการตั้งค่าสำหรับ News Item
      itemBackgroundColor: "#ffffff",
      itemBorderColor: "#e1e5e9",
      itemHoverBorderColor: "#2874fc",
      itemTitleColor: "#333333",
      itemTitleHoverColor: "#2874fc",
      itemDateColor: "#2874fc",
      itemMetaColor: "#888888",
      itemCategoryBackground: "#2874fc",
      itemCategoryTextColor: "#ffffff"
    };

    // ดึงข้อมูลจาก data attributes
    const defaultCategory = container.getAttribute("data-category") || "ทั้งหมด";
    const limit = container.getAttribute("data-limit") || 10;

    // สร้างปุ่มและเพิ่ม event listener
    const buttonsContainer = document.createElement("div");
    buttonsContainer.className = "rmu-news-buttons";
    buttonsData.forEach(({
      label,
      category
    }) => {
      const btn = document.createElement("button");
      btn.textContent = label;
      btn.className = category === defaultCategory ? "active" : "";
      btn.style.backgroundColor = settings.buttonColor;
      btn.style.color = settings.buttonTextColor;
      btn.style.borderColor = settings.borderColor;
      btn.addEventListener("mouseover", function () {
        if (!btn.classList.contains("active")) {
          btn.style.backgroundColor = settings.buttonHoverColor;
          btn.style.color = settings.buttonHoverTextColor;
        }
      });
      btn.addEventListener("mouseout", function () {
        if (!btn.classList.contains("active")) {
          btn.style.backgroundColor = settings.buttonColor;
          btn.style.color = settings.buttonTextColor;
        }
      });
      btn.addEventListener("click", function () {
        // ลบ active class จากปุ่มอื่น
        buttonsContainer.querySelectorAll("button").forEach(b => {
          b.classList.remove("active");
          b.style.backgroundColor = settings.buttonColor;
          b.style.color = settings.buttonTextColor;
        });

        // เพิ่ม active class ให้ปุ่มปัจจุบัน
        btn.classList.add("active");
        btn.style.backgroundColor = settings.buttonHoverColor;
        btn.style.color = settings.buttonHoverTextColor;
        fetchAndRenderNews(category, container);
      });
      buttonsContainer.appendChild(btn);
    });

    // แทรกปุ่มหลังจาก search input
    const searchContainer = container.querySelector(".rmu-news-search-container");
    if (searchContainer) {
      searchContainer.appendChild(buttonsContainer);
    } else {
      container.insertBefore(buttonsContainer, container.firstChild);
    }

    // เพิ่ม search functionality
    const searchInput = container.querySelector("#rmu-news-search");
    if (searchInput) {
      let searchTimeout;
      searchInput.addEventListener("input", function () {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.trim();
        searchTimeout = setTimeout(() => {
          const activeButton = buttonsContainer.querySelector("button.active");
          const activeCategory = activeButton ? buttonsData.find(btn => btn.label === activeButton.textContent)?.category : defaultCategory;
          fetchAndRenderNews(activeCategory, container, searchTerm);
        }, 500);
      });
    }

    // ฟังก์ชันดึงข้อมูลข่าวและแสดงผล
    function fetchAndRenderNews(category, targetContainer, searchTerm = "") {
      const loadingElement = targetContainer.querySelector(".rmu-news-loading");
      const contentElement = targetContainer.querySelector(".rmu-news-content") || targetContainer;

      // แสดง loading
      if (loadingElement) {
        loadingElement.style.display = "block";
      }

      // สร้าง API endpoint โดยใช้ apiUrl จากการตั้งค่า
      let apiEndpoint;
      const baseUrl = settings.apiUrl || "https://www.rmu.ac.th/api/posts/filter";
      if (searchTerm) {
        // สำหรับกรองข้อมูลตามคำค้นหาจาก input
        apiEndpoint = `${baseUrl}?post=${encodeURIComponent(searchTerm)}`;
      } else if (category && category !== "ทั้งหมด") {
        // สำหรับหมวดหมู่เฉพาะ
        apiEndpoint = `${baseUrl}?category=${encodeURIComponent(category)}`;
      } else {
        // สำหรับโพสต์ทั้งหมด
        apiEndpoint = baseUrl;
      }

      // ล้างข่าวเก่าออกก่อน
      const oldNews = contentElement.querySelectorAll(".rmu-news-item");
      oldNews.forEach(el => el.remove());
      fetch(apiEndpoint).then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      }).then(data => {
        // ซ่อน loading
        if (loadingElement) {
          loadingElement.style.display = "none";
        }
        if (data.status === "success" && data.data) {
          const articles = data.data;

          // ตรวจสอบค่าจาก data-limit
          const limit = parseInt(targetContainer.getAttribute("data-limit")) || 0;
          const limitedArticles = limit > 0 ? articles.slice(0, limit) : articles;
          if (limitedArticles.length === 0) {
            const noData = document.createElement("div");
            noData.className = "rmu-news-item rmu-news-no-data";
            noData.textContent = searchTerm ? `ไม่พบข่าวที่ค้นหา "${searchTerm}" ในหมวด ${category}` : `ไม่มีข่าวในหมวด ${category}`;
            contentElement.appendChild(noData);
            return;
          }
          limitedArticles.forEach(article => {
            const date = new Date(article.created_at);
            const formattedDate = `${date.getDate()} ${["ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."][date.getMonth()]} ${date.getFullYear() + 543}`;
            const articleElement = document.createElement("div");
            articleElement.className = "rmu-news-item";

            // ใช้สีจากการตั้งค่า
            articleElement.style.backgroundColor = settings.itemBackgroundColor;
            articleElement.style.borderColor = settings.itemBorderColor;

            // สร้าง URL สำหรับลิงก์โดยใช้ baseUrl
            const baseUrlForLink = settings.apiUrl ? settings.apiUrl.replace("/api/posts/filter", "") : "https://www.rmu.ac.th";

            // กำหนดหมวดหมู่
            const categoryTags = `<span class="rmu-news-category-span" style="
                                background: ${settings.itemCategoryBackground};
                                color: ${settings.itemCategoryTextColor};
                                box-shadow: 0 2px 4px ${hexToRgba(settings.itemCategoryBackground, 0.3)};
                            ">${escapeHtml(article.category_name)}</span>`;
            articleElement.innerHTML = `
                                <div class="rmu-news-body">
                                    <div class="rmu-news-date-meta">
                                        <div class="rmu-news-date" style="
                                            color: ${settings.itemDateColor};
                                            background: ${hexToRgba(settings.itemDateColor, 0.1)};
                                            border-color: ${hexToRgba(settings.itemDateColor, 0.2)};
                                        ">${formattedDate}</div>
                                        <div class="rmu-news-meta" style="
                                            color: ${settings.itemMetaColor};
                                        ">เปิดอ่าน ${article.count_view || 0} ครั้ง</div>
                                    </div>
                                    <div class="rmu-news-title">
                                        <a href="${baseUrlForLink}/single/${article.id}/post" 
                                           target="_blank" 
                                           rel="noopener"
                                           style="color: ${settings.itemTitleColor};"
                                           onmouseover="this.style.color='${settings.itemTitleHoverColor}'"
                                           onmouseout="this.style.color='${settings.itemTitleColor}'"
                                        >${escapeHtml(article.topic)}</a>
                                    </div>
                                    <div class="rmu-news-category">${categoryTags}</div>
                                </div>
                            `;

            // เพิ่ม hover effect สำหรับ border
            articleElement.addEventListener("mouseenter", function () {
              this.style.borderColor = settings.itemHoverBorderColor;
            });
            articleElement.addEventListener("mouseleave", function () {
              this.style.borderColor = settings.itemBorderColor;
            });
            contentElement.appendChild(articleElement);
          });
        } else {
          throw new Error(data.message || "ไม่สามารถโหลดข้อมูลได้");
        }
      }).catch(error => {
        console.error("RMU News Error:", error);

        // ซ่อน loading
        if (loadingElement) {
          loadingElement.style.display = "none";
        }
        const errorMsg = document.createElement("div");
        errorMsg.className = "rmu-news-item rmu-news-error";
        errorMsg.textContent = "เกิดข้อผิดพลาดในการโหลดข่าว: " + error.message;
        contentElement.appendChild(errorMsg);
      });
    }

    // โหลดข่าวหมวดแรก (หรือตาม default category) ตอนโหลดหน้าเว็บครั้งแรก
    fetchAndRenderNews(defaultCategory, container);
  }

  // Helper functions
  function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  // Helper function to convert hex to rgba
  function hexToRgba(hex, alpha) {
    if (!hex) return `rgba(0,0,0,${alpha})`;
    hex = hex.replace("#", "");
    if (hex.length === 3) {
      hex = hex.split("").map(char => char + char).join("");
    }
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
  }
});
/******/ })()
;
//# sourceMappingURL=view.js.map