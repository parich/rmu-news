/******/ (() => { // webpackBootstrap
/*!******************************!*\
  !*** ./src/rmu-news/view.js ***!
  \******************************/
// file: src\rmu-news\view.js
document.addEventListener("DOMContentLoaded", function () {
  const container = document.getElementById("rmu-news-container");
  const buttonsData = [{
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

  // สร้างปุ่มและเพิ่ม event listener
  const buttonsContainer = document.createElement("div");
  buttonsContainer.className = "rmu-news-buttons";
  buttonsData.forEach(({
    label,
    category
  }) => {
    const btn = document.createElement("button");
    btn.textContent = label;
    btn.addEventListener("click", () => {
      fetchAndRenderNews(category);
    });
    buttonsContainer.appendChild(btn);
  });
  container.appendChild(buttonsContainer);

  // ฟังก์ชันดึงข้อมูลข่าวและแสดงผล
  function fetchAndRenderNews(category) {
    const apiEndpoint = `https://www.rmu.ac.th/api/posts/filter?category=${encodeURIComponent(category)}`;

    // ล้างข่าวเก่าออกก่อน
    // ลบข่าวที่มี class rmu-news-item ทั้งหมด
    const oldNews = container.querySelectorAll(".rmu-news-item");
    oldNews.forEach(el => el.remove());
    fetch(apiEndpoint).then(response => response.json()).then(data => {
      if (data.status === "success") {
        const articles = data.data;
        if (articles.length === 0) {
          const noData = document.createElement("div");
          noData.className = "rmu-news-item";
          noData.textContent = "ไม่มีข่าวในหมวดนี้";
          container.appendChild(noData);
          return;
        }
        articles.forEach(article => {
          const date = new Date(article.created_at);
          const formattedDate = `${date.getDate()} ${["ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."][date.getMonth()]} ${date.getFullYear() + 543}`;
          const articleElement = document.createElement("div");
          articleElement.className = "rmu-news-item";
          articleElement.innerHTML = `
                            <div class="rmu-news-date">${formattedDate}</div>
                            <div class="rmu-news-title">
                                <a href="https://www.rmu.ac.th/single/${article.id}/post" target="_blank">${article.topic}</a>
                            </div>
                            <div class="rmu-news-meta">เปิดอ่าน ${article.count_view} ครั้ง</div>
                        `;
          container.appendChild(articleElement);
        });
      } else {
        const errorMsg = document.createElement("div");
        errorMsg.className = "rmu-news-item";
        errorMsg.textContent = "ไม่สามารถโหลดข่าวได้";
        container.appendChild(errorMsg);
      }
    }).catch(error => {
      const errorMsg = document.createElement("div");
      errorMsg.className = "rmu-news-item";
      errorMsg.textContent = "เกิดข้อผิดพลาด: " + error;
      container.appendChild(errorMsg);
    });
  }

  // โหลดข่าวหมวดแรก (สมัครงาน) ตอนโหลดหน้าเว็บครั้งแรก
  fetchAndRenderNews(buttonsData[0].category);
});
/******/ })()
;
//# sourceMappingURL=view.js.map