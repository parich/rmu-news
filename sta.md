```
└── 📁rmu-news
    └── 📁build
        └── blocks-manifest.php
        └── 📁rmu-news
            └── block.json
            └── index-rtl.css
            └── index.asset.php
            └── index.css
            └── index.css.map
            └── index.js
            └── index.js.map
            └── render.php
            └── style-index-rtl.css
            └── style-index.css
            └── style-index.css.map
            └── view.asset.php
            └── view.js
            └── view.js.map
    └── 📁src
        └── 📁rmu-news
            └── block.json
            └── edit.js
            └── editor.scss
            └── index.js
            └── render.php
            └── style.scss
            └── view.js
    └── .editorconfig
    └── .gitignore
    └── package-lock.json
    └── package.json
    └── readme.txt
    └── rmu-news.php
```

## api route

- /api/posts/filter?start_date=2025-06-01&end_date=2025-06-15&post=ข่าว&user=สมชาย&faculty=วิศวกรรมศาสตร์&category=ประกาศ
- /api/posts/filter?start_date=2025-06-01&end_date=2025-06-15
- /api/posts/filter?post=ข่าว
- /api/posts/filter?user=สมชาย
- /api/posts/filter?faculty=วิศวกรรมศาสตร์
- /api/posts/filter?category=ประกาศ
- /api/posts/filter?start_date=2025-06-01&end_date=2025-06-15&post=ข่าว&user=สมชาย&faculty=วิศวกรรมศาสตร์&category=ประกาศ

## Install

- npx @wordpress/create-block@latest post-rmu-news --variant dynamic --target-dir .

## ON DEV : you can run several commands inside:

$ npm start
Starts the build for development.

$ npm run build
Builds the code for production.

$ npm run format
Formats files.

$ npm run lint:css
Lints CSS files.

$ npm run lint:js
Lints JavaScript files.

$ npm run plugin-zip
Creates a zip file for a WordPress plugin.

$ npm run packages-update
Updates WordPress packages to the latest version.

To enter the directory type:

$ cd post-rmu-news

You can start development with:

$ npm start

## db insert

- rmu_news_api_url
- rmu_news_button_color
- rmu_news_button_text_color
- rmu_news_button_border_color
- rmu_news_button_hover_color
- rmu_news_button_hover_text_color'
