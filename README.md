# Vite Helper (highly opinionated)

Can only be used together with our internal setup.

## Installation

```bash
composer require andersundsehr/vite_helper
```

## Usage

add this to your  `page.typoscript`

````typoscript
page.headTag.stdWrap.postUserFunc = AUS\ViteHelper\ViteUtility->headTag
page.headTag.stdWrap.postUserFunc {
    fontPreLoad {
      0 = src/fonts/Iconfont/icomoon.ttf
      1 = src/fonts/lato-v23-latin-300.woff2
      2 = src/fonts/lato-v23-latin-700.woff2
      3 = src/fonts/lato-v23-latin-regular.woff2
    }
  css {
    0 = src/typescript/index.css
  }
  js {
    0 = src/typescript/index.ts
  }
}
````

you need to have a `vite.config.js` in your project root

# with ♥️ from anders und sehr GmbH

> If something did not work 😮  
> or you appreciate this Extension 🥰 let us know.

> We are hiring https://www.andersundsehr.com/karriere/
