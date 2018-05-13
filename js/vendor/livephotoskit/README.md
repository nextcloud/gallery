# LivePhotosKit JS

Use the LivePhotosKit JS library to play Live Photos on your web pages.

## Overview

The JavaScript API presents the player in the form of a DOM element, much
like an `image` or `video` tag, which can be configured with photo and video
resources and other options, and have its playback controlled either
programmatically by the consuming developer, or via pre-provided controls
by the browsing end-user.

## Documentation

For more detailed documentation, see
https://developer.apple.com/reference/livephotoskitjs

For information about Live Photos for developers, see https://developer.apple.com/live-photos

## Installation

### Using npm

Install and save LivePhotosKit JS:
```bash
npm install --save livephotoskit
```

Import LivePhotosKit JS (ES6/TypeScript):

```javascript
import * as LivePhotosKit from 'livephotoskit';
```

A [TypeScript](https://www.typescriptlang.org) type definition file is included in the 
distribution and will automatically be picked up by the TypeScript compiler.

### Using the Apple CDN

```html
<script src="https://cdn.apple-livephotoskit.com/lpk/1/livephotoskit.js"></script>
```

A [TypeScript](https://www.typescriptlang.org) type definition file is available at
```html
https://cdn.apple-livephotoskit.com/lpk/1/livephotoskit.d.ts
```

> Note
> 
> The LivePhotosKit JS version number is in the URL. For example, 1 specifies LivePhotosKit JS 1.x.x.

You will then be able to access LivePhotosKit JS via `window.LivePhotosKit`.


## Browser Compatibility

The LivePhotosKit JS player supports the following browsers:

- On macOS: Safari, Chrome, Firefox
- On iOS: Safari, Chrome
- On Windows: Chrome, Firefox, Edge, Internet Explorer 11
- On Android: Chrome (Performance depends on device)
