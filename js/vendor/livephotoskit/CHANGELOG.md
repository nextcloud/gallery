# Change Log

## v1.5.4 (2018-03-29)

- Included better accessibility support for LivePhotosKit JS via a number of `aria-label` and `role` attributes.
- Added a `caption` API which gives developers the ability to add accessibile descriptions to their images.

## v1.5.2 (2017-10-19)

- Fixed a rare unhandled exception in Safari.
- Added a `LivePhotosKit.MASTERING_NUMBER` property which indicates precisely which build version is being used.

## v1.5.1 (2017-09-12)

- Bugfixes for badge re-drawing.

## v1.5.0 (2017-09-12)

- New `createPlayer` and `augmentElementAsPlayer` methods.
- Added support for new iOS 11 Live Photo Effects: Loop, Bounce, and Long Exposure.
- Added support for graceful HTML `img` tag fallback with NoScript clients.

## v1.4.11 (2017-07-20)

- Laid groundwork for localizing badge strings.
- No longer check for `photoTime` unless playing back a Live Photo `hint`.
- Improved a number of type checks.

## v1.4.10 (2017-06-05)

- Fixed intermittent playback issues in Mozilla Firefox and Internet Explorer 11.
- Updated Live Photo playback to match new playback style introduced in iOS 10.3.1.

## v1.4.9 (2017-05-01)

- Live Photo player is no longer selectable. This addresses the issue where a selection
  bubble would appear on iOS Safari during a long press.
- Fixed an intermittent memory exhaustion crash.

## v1.4.8 (2017-04-22)

- When a `Player` has a zero width, height or both, a warning is emitted to the console.
- Corrected `Player.updateSize()` function declaration.
- Reduced size of `livephotoskit.js` distributable.
- Moved documentation links to be more prominent in `README.md`.

## v1.4.6 (2017-04-20)

- Changed home page URL.
- Added reference to home page in `README.md`.

## v1.4.5 (2017-04-20)

- Fixed mangled copyright symbol.

## v1.4.4 (2017-04-20)

- Initial release.
