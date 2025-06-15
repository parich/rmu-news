document.addEventListener("DOMContentLoaded", function () {
	const container = document.getElementById("rmu-news-container");
	const apiEndpoint = "https://www.rmu.ac.th/api/posts"; // URL can be dynamic if needed

	fetch(apiEndpoint)
		.then((response) => response.json())
		.then((data) => {
			if (data.status === "success") {
				console.log(data);
				const articles = data.data;
				articles.forEach((article) => {
					const articleElement = document.createElement("div");
					articleElement.className = "rmu-news-article";
					articleElement.innerHTML = `
                        <h2>${article.topic}</h2>
                        <p>${article.detail}</p>
                        <a href="${article.urlto || "#"}">Read more</a>
                    `;
					container.appendChild(articleElement);
				});
			} else {
				container.textContent = "Failed to load news.";
			}
		})
		.catch((error) => {
			container.textContent = "Error fetching data: " + error;
		});
});
