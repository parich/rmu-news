// file: src/rmu-news/view.js
// โหลดเสร็จสมบูรณ์ก่อนเริ่มการทำงานของ script
document.addEventListener("DOMContentLoaded", function () {
	// ค้นหา container ทั้งหมดที่มี class rmu-news-shortcode
	const containers = document.querySelectorAll(
		".rmu-news-shortcode:not([data-initialized])",
	);

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
		const buttonsData = [
			{ label: "ทั้งหมด", category: "ทั้งหมด" },
			{ label: "ข่าวสมัครงาน", category: "สมัครงาน" },
			{ label: "ข่าวประกวดราคา", category: "ประกวดราคา" },
			{ label: "ข่าวประชาสัมพันธ์", category: "ประชาสัมพันธ์" },
			{ label: "ข่าวบริการ", category: "บริการ" },
			{ label: "ข่าวพระวุณ", category: "สวัสดิการ" },
			{ label: "ข่าวสรรหา", category: "สรรหา" },
		];

		// ดึงการตั้งค่าจาก WordPress
		const settings = window.rmuNewsSettings || {
			apiUrl: "",
			activeColor: "",
			buttonTextColor: "",
		};

		// ดึงข้อมูลจาก data attributes
		const defaultCategory =
			container.getAttribute("data-category") || "ทั้งหมด";
		const limit = container.getAttribute("data-limit") || 10;

		// สร้างปุ่มและเพิ่ม event listener
		const buttonsContainer = document.createElement("div");
		buttonsContainer.className = "rmu-news-buttons";

		buttonsData.forEach(({ label, category }) => {
			const btn = document.createElement("button");
			btn.textContent = label;
			btn.className = category === defaultCategory ? "active" : "";
			btn.style.backgroundColor = settings.buttonColor;
			btn.style.color = settings.buttonTextColor;
			btn.style.borderColor = settings.borderColor;

			btn.addEventListener("click", function () {
				// ลบ active class จากปุ่มอื่น
				buttonsContainer.querySelectorAll("button").forEach((b) => {
					b.classList.remove("active");
					b.style.backgroundColor = settings.buttonColor;
				});

				// เพิ่ม active class ให้ปุ่มปัจจุบัน
				btn.classList.add("active");
				btn.style.backgroundColor = "#000";

				fetchAndRenderNews(category, container);
			});

			buttonsContainer.appendChild(btn);
		});

		// แทรกปุ่มหลังจาก search input
		const searchContainer = container.querySelector(
			".rmu-news-search-container",
		);
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
					const activeCategory = activeButton
						? buttonsData.find((btn) => btn.label === activeButton.textContent)
								?.category
						: defaultCategory;

					fetchAndRenderNews(activeCategory, container, searchTerm);
				}, 500);
			});
		}

		// ฟังก์ชันดึงข้อมูลข่าวและแสดงผล
		function fetchAndRenderNews(category, targetContainer, searchTerm = "") {
			const loadingElement = targetContainer.querySelector(".rmu-news-loading");
			const contentElement =
				targetContainer.querySelector(".rmu-news-content") || targetContainer;

			// แสดง loading
			if (loadingElement) {
				loadingElement.style.display = "block";
			}

			// สร้าง API endpoint ตามที่ระบุ
			let apiEndpoint;
			if (searchTerm) {
				// สำหรับกรองข้อมูลตามคำค้นหาจาก input
				apiEndpoint = `https://www.rmu.ac.th/api/posts/filter?post=${encodeURIComponent(
					searchTerm,
				)}`;
			} else if (category && category !== "ทั้งหมด") {
				// สำหรับหมวดหมู่เฉพาะ
				apiEndpoint = `https://www.rmu.ac.th/api/posts/filter?category=${encodeURIComponent(
					category,
				)}`;
			} else {
				// สำหรับโพสต์ทั้งหมด
				apiEndpoint = `https://www.rmu.ac.th/api/posts/filter`;
			}

			// ล้างข่าวเก่าออกก่อน
			const oldNews = contentElement.querySelectorAll(".rmu-news-item");
			oldNews.forEach((el) => el.remove());

			fetch(apiEndpoint)
				.then((response) => {
					if (!response.ok) {
						throw new Error(`HTTP error! status: ${response.status}`);
					}
					return response.json();
				})
				.then((data) => {
					// ซ่อน loading
					if (loadingElement) {
						loadingElement.style.display = "none";
					}

					if (data.status === "success" && data.data) {
						const articles = data.data;

						// ตรวจสอบค่าจาก data-limit
						const limit =
							parseInt(targetContainer.getAttribute("data-limit")) || 0;
						const limitedArticles =
							limit > 0 ? articles.slice(0, limit) : articles;

						if (limitedArticles.length === 0) {
							const noData = document.createElement("div");
							noData.className = "rmu-news-item rmu-news-no-data";
							noData.textContent = searchTerm
								? `ไม่พบข่าวที่ค้นหา "${searchTerm}" ในหมวด ${category}`
								: `ไม่มีข่าวในหมวด ${category}`;
							contentElement.appendChild(noData);
							return;
						}

						limitedArticles.forEach((article) => {
							const date = new Date(article.created_at);
							const formattedDate = `${date.getDate()} ${
								[
									"ม.ค.",
									"ก.พ.",
									"มี.ค.",
									"เม.ย.",
									"พ.ค.",
									"มิ.ย.",
									"ก.ค.",
									"ส.ค.",
									"ก.ย.",
									"ต.ค.",
									"พ.ย.",
									"ธ.ค.",
								][date.getMonth()]
							} ${date.getFullYear() + 543}`;

							const articleElement = document.createElement("div");
							articleElement.className = "rmu-news-item";

							// กำหนดหมวดหมู่
							const categoryTags = `<span class="rmu-news-category">${escapeHtml(
								article.category_name,
							)}</span>`;

							articleElement.innerHTML = `
        <div class="rmu-news-body">
            <div class="rmu-news-date-meta">
                <div class="rmu-news-date">${formattedDate}</div>
                <div class="rmu-news-meta">เปิดอ่าน ${
									article.count_view || 0
								} ครั้ง</div>
            </div>
            <div class="rmu-news-title">
                <a href="https://www.rmu.ac.th/single/${
									article.id
								}/post" target="_blank" rel="noopener">${escapeHtml(
									article.topic,
								)}</a>
            </div>
            <div class="rmu-news-category">${categoryTags}</div>
        </div>
    `;
							contentElement.appendChild(articleElement);
						});
					} else {
						throw new Error(data.message || "ไม่สามารถโหลดข้อมูลได้");
					}
				})
				.catch((error) => {
					console.error("RMU News Error:", error);
					// ซ่อน loading
					if (loadingElement) {
						loadingElement.style.display = "none";
					}
					const errorMsg = document.createElement("div");
					errorMsg.className = "rmu-news-item rmu-news-error";
					errorMsg.textContent =
						"เกิดข้อผิดพลาดในการโหลดข่าว: " + error.message;
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

	function darkenColor(color, percent) {
		const num = parseInt(color.replace("#", ""), 16);
		const amt = Math.round(2.55 * percent);
		const R = (num >> 16) - amt;
		const G = ((num >> 8) & 0x00ff) - amt;
		const B = (num & 0x0000ff) - amt;
		return (
			"#" +
			(
				0x1000000 +
				(R < 255 ? (R < 1 ? 0 : R) : 255) * 0x10000 +
				(G < 255 ? (G < 1 ? 0 : G) : 255) * 0x100 +
				(B < 255 ? (B < 1 ? 0 : B) : 255)
			)
				.toString(16)
				.slice(1)
		);
	}
});
