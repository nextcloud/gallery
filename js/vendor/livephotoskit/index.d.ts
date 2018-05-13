/*
Copyright (c) 2017 Apple Inc. All rights reserved.

# LivePhotosKit JS License

**IMPORTANT:** This Apple LivePhotosKit software is supplied to you by Apple
Inc. ("Apple") in consideration of your agreement to the following terms, and
your use, reproduction, or installation of this Apple software constitutes
acceptance of these terms. If you do not agree with these terms, please do not
use, reproduce or install this Apple software.

This Apple LivePhotosKit software is supplied to you by Apple Inc. ("Apple") in
consideration of your agreement to the following terms, and your use,
reproduction, or installation of this Apple software constitutes acceptance of
these terms. If you do not agree with these terms, please do not use, reproduce
or install this Apple software.

This software is licensed to you only for use with LivePhotos that you are
authorized or legally permitted to embed or display on your website. 

The LivePhotosKit Software is only licensed and intended for the purposes set
forth above and may not be used for other purposes or in other contexts without
Apple's prior written permission. For the sake of clarity, you may not and
agree not to or enable others to, modify or create derivative works of the
LivePhotosKit Software.

Neither the name, trademarks, service marks or logos of Apple Inc. may be used
to endorse or promote products, services without specific prior written
permission from Apple. Except as expressly stated in this notice, no other
rights or licenses, express or implied, are granted by Apple herein.

The LivePhotosKit Software is provided by Apple on an "AS IS" basis. APPLE
MAKES NO WARRANTIES, EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE
IMPLIED WARRANTIES OF NON-INFRINGEMENT, MERCHANTABILITY AND FITNESS FOR A
PARTICULAR PURPOSE, REGARDING THE LIVEPHOTOSKIT SOFTWARE OR ITS USE AND
OPERATION ALONE OR IN COMBINATION WITH YOUR PRODUCTS, SYSTEMS, OR SERVICES.
APPLE DOES NOT WARRANT THAT THE LIVEPHOTOSKIT SOFTWARE WILL MEET YOUR
REQUIREMENTS, THAT THE OPERATION OF THE LIVEPHOTOSKIT SOFTWARE WILL BE
UNINTERRUPTED OR ERROR-FREE, THAT DEFECTS IN THE LIVEPHOTOSKIT SOFTWARE WILL BE
CORRECTED, OR THAT THE LIVEPHOTOSKIT SOFTWARE WILL BE COMPATIBLE WITH FUTURE
APPLE PRODUCTS, SOFTWARE OR SERVICES. NO ORAL OR WRITTEN INFORMATION OR ADVICE
GIVEN BY APPLE OR AN APPLE AUTHORIZED REPRESENTATIVE WILL CREATE A WARRANTY. 

IN NO EVENT SHALL APPLE BE LIABLE FOR ANY DIRECT, SPECIAL, INDIRECT, INCIDENTAL
OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) RELATING TO OR ARISING IN ANY WAY OUT OF THE USE, REPRODUCTION,
OR INSTALLATION, OF THE LIVEPHOTOSKIT SOFTWARE BY YOU OR OTHERS, HOWEVER CAUSED
AND WHETHER UNDER THEORY OF CONTRACT, TORT (INCLUDING NEGLIGENCE), STRICT
LIABILITY OR OTHERWISE, EVEN IF APPLE HAS BEEN ADVISED OF THE POSSIBILITY OF
SUCH DAMAGE. SOME JURISDICTIONS DO NOT ALLOW THE LIMITATION OF LIABILITY FOR
PERSONAL INJURY, OR OF INCIDENTAL OR CONSEQUENTIAL DAMAGES, SO THIS LIMITATION
MAY NOT APPLY TO YOU. In no event shall Apple's total liability to you for all
damages (other than as may be required by applicable law in cases involving
personal injury) exceed the amount of fifty dollars ($50.00). The foregoing
limitations will apply even if the above stated remedy fails of its essential
purpose. 


**ACKNOWLEDGEMENTS:**
https://cdn.apple-livephotoskit.com/lpk/1/acknowledgements.txt

v1.5.4
*/


/**
 * The LivePhotosKit JS build number.
 */
export declare const BUILD_NUMBER: string;

/**
 * The version of LivePhotosKit JS.
 */
export declare const VERSION: string;

/**
 * An event that is fired when LivePhotosKit JS is loaded. Used when `livephotoskit.js` is loaded asynchronously.
 * It will always have the value `livephotoskitloaded`.
 * @example
 * document.addEventListener('livephotoskitloaded', function(ev: Event): void {
 *     console.log('Loaded LivePhotosKit JS');
 * });
 */
export declare const LIVEPHOTOSKIT_LOADED: string;

export interface PlaybackStyle {
    /**
     * Default fallback value.
     */
    'default': PlaybackStyleLiteral;
    /**
     * Plays back the entire motion content of the Live Photo, including
     * transition effects at the start and end.
     * @const {String} LivePhotosKit.PlaybackStyle.FULL
     */
    FULL: PlaybackStyleLiteral;
    /**
     * Plays back only a brief section of the motion content of the Live Photo.
     * @const {String} LivePhotosKit.PlaybackStyle.HINT
     */
    HINT: PlaybackStyleLiteral;
    /**
     *
     * @const {String} LivePhotosKit.PlaybackStyle.LOOP
     */
    LOOP: PlaybackStyleLiteral;
}
/**
 * Playback options for Live Photos.
 *
 * @class LivePhotosKit.PlaybackStyle
 * @enumlike
 * @private
 */
export declare const PlaybackStyle: PlaybackStyle;
/**
 * Playback option type for Live Photos.
 */
export declare type PlaybackStyleLiteral = 'full' | 'hint' | 'loop';

export interface EffectType {
    /**
     * Default fallback value. Maps to the "LIVE" EffectType.
     * @const {Object} LivePhotosKit.EffectType.default
     */
    readonly 'default': EffectTypeLiteral;
    /**
     * The "BOUNCE" effect type which plays a looping effect forward and
     * backward smoothly. Will play/loop automatically.
     * @const {String} LivePhotosKit.EffectType.BOUNCE
     */
    readonly BOUNCE: EffectTypeLiteral;
    /**
     * The "EXPOSURE" effect type plays like a typical "LIVE" Live Photo.
     * However, the keyframe is taken from a composite of frames to form a
     * long-exposure-esque blurred image.
     * @const {String} LivePhotosKit.EffectType.EXPOSURE
     */
    readonly EXPOSURE: EffectTypeLiteral;
    /**
     * The traditional "LIVE" Live Photo effect. A still keyframe with a video
     * that plays back when the badge is interacted with.
     * @const {String} LivePhotosKit.EffectType.LIVE
     */
    readonly LIVE: EffectTypeLiteral;
    /**
     * The "LOOP" effect type which plays a looping video. Will play/loop
     * automatically.
     * @const {String} LivePhotosKit.EffectType.LOOP
     */
    readonly LOOP: EffectTypeLiteral;
}

export interface EffectTypePrivate {
    readonly _mappingToLocalizedStrings: {
        readonly live: string;
        readonly bounce: string;
        readonly exposure: string;
        readonly loop: string;
    };
    readonly _mappingToPlaybackStyle: {
        readonly bounce: PlaybackStyleLiteral;
        readonly exposure: PlaybackStyleLiteral;
        readonly live: PlaybackStyleLiteral;
        readonly loop: PlaybackStyleLiteral;
    };
    toBadgeText(effectType: EffectTypeLiteral): string;
    toLocalizedString(effectType: EffectTypeLiteral): string;
    toPlaybackStyle(effectType: EffectTypeLiteral): PlaybackStyleLiteral;
}

/**
 * Effect types for Live Photos.
 *
 * @class LivePhotosKit.EffectType
 * @enumlike
 * @private
 */
export declare const EffectType: EffectType & EffectTypePrivate;

/**
 * Valid values for the type of Live Photo effect that is being used.
 */
export declare type EffectTypeLiteral = 'bounce' | 'exposure' | 'live' | 'loop';

/**
 * Error codes.
 */
export declare enum Errors {
    /**
     * Internal use only.
     */
    FAILED_TO_DOWNLOAD_RESOURCE = 0,
        /**
         * Error code for when the photo component of the Live Photo is unable to be loaded. This could be due to
         * network conditions or an inability to decode the image.
         * @const {number} LivePhotosKit.Errors.PHOTO_FAILED_TO_LOAD
         */
    PHOTO_FAILED_TO_LOAD = 1,
        /**
         * Error code for when the video component of the Live Photo is unable to be loaded. This could be due to
         * network conditions or an inability to decode the video.
         * @const {number} LivePhotosKit.Errors.VIDEO_FAILED_TO_LOAD
         */
    VIDEO_FAILED_TO_LOAD = 2,
}

export declare interface PlayerProps {
    /**
     * The current timestamp, as a floating-point number of seconds from the beginning
     * of the animation, to display on the screen.
     */
    currentTime: number;
    /**
     * When working with a looping Live Photo (of the "bounce" or "loop"
     * variety), determines whether the asset should begin looping immediately
     * upon entering a playable state. When applied to non-looping assets this
     * property will have no effect.
     */
    autoplay: boolean;
    /**
     * The type of Live Photo effect currently being used. This can be
     * defined up-front from one of the following 4 values:
     * - `{@link LivePhotosKit~EffectType.LIVE LivePhotosKit.EffectType.LIVE}`
     * - `{@link LivePhotosKit~EffectType.EXPOSURE LivePhotosKit.EffectType.EXPOSURE}`
     * - `{@link LivePhotosKit~EffectType.LOOP LivePhotosKit.EffectType.LOOP}`
     * - `{@link LivePhotosKit~EffectType.BOUNCE LivePhotosKit.EffectType.BOUNCE}`
     *
     * `LIVE` refers to the traditional Live Photo and leverages the "full"
     * playback recipe.
     *
     * `EXPOSURE` plays just like a traditional Live Photo and leverages the
     * "full" playback recipe. However, the keyframe for the photo is a
     * composite of the video frames used to create a blurred "long exposure"
     * like effect.
     *
     * `LOOP` refers to a Live Photo which continuously loops and fades into
     * itself creating a seamless effect.
     *
     * `BOUNCE` refers to a Live Photo which continuously loops and plays both
     * forward and backward seamlessly.
     */
    effectType: EffectTypeLiteral;
    /**
     * The style of playback to determine the nature of the animation.
     */
    playbackStyle: PlaybackStyleLiteral;
    /**
     * Whether or not the Player will download the bytes at the provided `videoSrc`
     * prior to the user or developer attempting to begin playback.
     */
    proactivelyLoadsVideo: boolean;
    /**
     * The source of the photo component of the Live Photo.
     */
    photoSrc: string | ArrayBuffer | null | undefined;
    /**
     * The MIME type of the photo asset, either as detected from the network request for it
     * (if it was provided as a URL on `photoSrc`) or
     * as provided by the developer prior to assigning a pre-prepared `ArrayBuffer` to `photoSrc`.
     */
    photoMimeType: string;
    /**
     * The source of the video component of the Live Photo.
     *
     * Set this one of three types of value:
     * - A string URL
     * - An `ArrayBuffer` instance that may already be available
     * - `null` (to clear the Player's video)
     */
    videoSrc: string | ArrayBuffer | null | undefined;
    /**
     * The MIME type of the video asset, either as detected from the network request for it
     * (if it was provided as a URL on `videoSrc`) or
     * as provided by the developer prior to assigning a pre-prepared `ArrayBuffer` to
     * `videoSrc`.
     */
    videoMimeType: string | null;
    /**
     * In the event that you have one version of the video asset you'd like to use with the
     * `videoSrc` for the Player, but that video asset
     * (whether due to format or transcoding or any other reason) doesn't include readable
     * `photoTime` and/or `frameTimes`
     * values, you may set this property to a string URL or `ArrayBuffer` featuring the
     * original QuickTime MOV file that was captured by iPhone. That file will be
     * parsed (in the same manner as those loaded via `videoSrc`) in order to serve as an
     * alternate source for the `photoTime` and `frameTimes` properties if they are not
     * otherwise available.
     */
    metadataVideoSrc: string | ArrayBuffer | null | undefined;
    /**
     * This is the actual renderable image-bearing DOM element (either an image or a
     * canvas) that the Player will consume in order to render itself to the screen.
     */
    photo: HTMLImageElement | HTMLCanvasElement | null | undefined;
    /**
     * This is the actual playable `HTMLVideoElement` that the Player will consume in
     * order to obtain video frame data to render to the screen while animating the
     * Live Photo.
     */
    video: HTMLVideoElement | null | undefined;
    /**
     * This is the timestamp, in seconds from the beginning of the provided video asset,
     * at which the still photo was captured.
     */
    photoTime: number | null | undefined;
    /**
     * This is an array of timestamps, each in seconds from the beginning of the
     * provided video asset, at which the individual video frames reside in the
     * video. This allows the Player to crossfade between video frames during
     * playback.
     */
    frameTimes: number[] | string | null | undefined;
    /**
     * This property takes on a value of either 0, 90, 180, or 270, and is
     * automatically determined as part of the video loading process.
     */
    videoRotation: number;
    /**
     * Whether or not the Apple-provided playback controls are enabled for the user.
     */
    showsNativeControls: boolean;
}

export interface PlayerPropsPrivate {
    preloadedEffectType: EffectTypeLiteral;
}

/**
 * Attach a LivePhotosKit instance to an existing DOM element.
 *
 * This instance provides a default native control allowing users to playback
 * Live Photos. The native control works as follows:
 *
 * On a desktop browser, when the user hovers the pointer over the native
 * control badge, the video starts playing until the user stops hovering or the
 * video ends. If the user moves the pointer before the video ends, the video
 * stops smoothly.
 *
 * On a mobile browser, when the user presses the Live Photo, the video starts
 * playing until the user stops pressing or the video ends. Similarly to a
 * computer, when the user stops pressing, the video stops smoothly.
 *
 * In the case of a "loop" style asset, the video will load and auto-play by
 * default. In this scenario interacting with the badge will not affect
 * playback. However, if the "autoplay" attribute is explicitly set to "false",
 * ineracting with the controls will play/pause the loop, similar to the above.
 *
 * @function LivePhotosKit~augmentElementAsPlayer
 * @augments HTMLElement
 *
 * @param The target DOM element to be decorated
 *     with additional properties and methods that allow it to act as the Player
 *     of Live Photos.
 *
 * @param An object containing keys and values for any subset
 *     of the public writable properties of Player. If provided, the values
 *     provided in this object will be used instead of the default values for
 *     those Player properties.
 *
 * @return The target element passed in, modified with additional properties and
 *     methods to allow it to act as a Live Photo player. If no target element
 *     was provided, then a new `HTMLDivElement` will be created and returned
 *     with Live Photo player functionality.
 */

export declare const augmentElementAsPlayer: (targetElement: HTMLElement, options?: Partial<PlayerProps>) => Player;

/**
 * Create a new DOM element and augment it with a Player instance.
 *
 * Leverages the {@link LivePhotosKit~augmentElementAsPlayer} function to
 * augment the new element.
 *
 * @function LivePhotosKit~createPlayer
 * @augments HTMLElement
 *
 * @param An object containing keys and values for any subset
 *     of the public writable properties of Player. If provided, the values
 *     provided in this object will be used instead of the default values for
 *     those Player properties.
 *
 * @return The target element passed in, modified with additional properties and
 *     methods to allow it to act as a Live Photo player. If no target element
 *     was provided, then a new `HTMLDivElement` will be created and returned
 *     with Live Photo player functionality.
 */
export declare const createPlayer: (options?: Partial<PlayerProps>) => Player;

/**
 * @deprecated This function will be deprecated in an upcoming release.
 * Use {@link LivePhotosKit~augmentElementAsPlayer} or {@link LivePhotosKit~createPlayer} instead.
 *
 * A function used to augment the target HTML element as a Live Photo player.
 *
 * @param The target DOM element to be decorated with additional properties and methods
 *   that allow it to act as the Player of Live Photos.
 *   If a target element is not passed, an `HTMLDivElement` will be created and used instead.
 *
 * @param An object containing keys and values for any subset of the public writable
 *   properties of Player. If provided, the values provided in this object will be used instead of the
 *   default values for those Player properties.
 *
 * @return The target element passed in, modified with additional properties and methods to allow it to act
 *   as a Live Photo player. If no target element was provided, then a new `HTMLDivElement` will be created and
 *   returned with Live Photo player functionality.
 */
export declare const Player: (targetElement?: HTMLElement, options?: Partial<PlayerProps>) => Player;

/**
 * The interface for an HTMLElement that has been augmented to play Live Photos.
 */
export declare interface Player extends HTMLElement, PlayerProps, PlayerPropsPrivate {
    /**
     * A value that indicates whether this is a Live Photo player.
     */
    readonly __isLPKPlayer__: boolean;
    /**
     * Plays the Live Photo animation using the `playbackStyle`.
     */
    play(): boolean;
    /**
     * Pauses the Live Photo animation at its current time.
     */
    pause(): void;
    /**
     * Stops playback entirely, and rewinds `currentTime` to zero.
     */
    stop(): void;
    /**
     * Toggles whether the Player is playing.
     */
    toggle(): void;
    /**
     * Update our Player's configuration/state.
     */
    setProperties(options: Partial<PlayerProps>): void;
    /**
     * When the Player is playing, this changes the duration such that the
     * playback will end prematurely. Playback will continue for a short
     * time while the animation gracefully winds down, but will take the
     * same or less time than it would have had this method not been invoked.
     */
    beginFinishingPlaybackEarly(): void;
    /**
     * Whether or not the Player is currently playing.
     */
    readonly isPlaying: boolean;
    /**
     * Whether the Player has been instructed to play.
     */
    readonly wantsToPlay: boolean;
    /**
     * The current timestamp, as a floating-point number of seconds from the beginning
     * of the animation, that is truly displayed on the screen.
     */
    readonly renderedTime: number;
    /**
     * The duration of the animation as a floating-point number of seconds, as determined
     * by the playbackStyle and the actual duration of the user-provided underlying video.
     */
    readonly duration: number;
    /**
     * Updates the internal layout of the rendered Live Photo to fit inside the
     * bounds of the Player's element. While this runs automatically as a result
     * of the window size changing, any time the Player element's size is changed
     * for any other reason (i.e. other DOM changes elsewhere in the page, with
     * dependent layout implications), this must be invoked in order to continue
     * to ensure the Live Photo's layout is correct.
     */
    updateSize(force: boolean): boolean;
    updateSize(displayWidth: number, displayHeight: number): boolean;
    updateSize(displayWidthOrForce?: number | boolean, displayHeight?: number): boolean;
    /**
     * The width, in pixels, of the underlying photo asset provided to the Player.
     */
    readonly photoWidth: number;
    /**
     * The height, in pixels, of the underlying photo asset provided to the Player.
     */
    readonly photoHeight: number;
    /**
     * The width, in pixels, of the underlying video asset provided to the Player.
     */
    readonly videoWidth: number;
    /**
     * The height, in pixels, of the underlying video asset provided to the Player.
     */
    readonly videoHeight: number;
    /**
     * This property denotes whether the Player is immediately able to begin
     * playback.
     */
    readonly canPlay: boolean;
    /**
     * A number between 0 and 1 that denotes the loading progress until it is
     * possible to start playback without any more delay.
     */
    readonly loadProgress: number;
    /**
     * If an error occurs while attempting to load required resources, or play
     * the Live Photo, the Error instance will land here at this property and
     * remain until the Player is given new resources or given the information
     * it lacked, and played again.
     */
    readonly errors: Error[];
    /**
     * Update properties on the player.
     *
     * @param options The options hash being passed through, from which to update our player.
     * @return Returns the newly updated options.
     */
    setProperties(options?: Partial<PlayerProps>): void;
}

export declare interface LivePhotoBadgeProps {
    /**
     * Reference to the canvas element backing the badge, can be passed in if
     * needed.
     * @type {HTMLCanvasElement}
     */
    element: HTMLCanvasElement;

    /**
     * Label text. Starts off as an empty string until we have loaded our video
     * resource and read the correct label from the metadata.
     * @type {String}
     */
    label: string;

    /**
     * Padding around the label.
     * @type {number}
     */
    labelPadding: number;

    /**
     * Padding between left edge and the circles.
     * @type {number}
     */
    leftPadding: number;

    /**
     * Desired height of the badge.
     * @type {number}
     */
    height: number;

    /**
     * CSS color string to represent the background of the badge.
     * @type {String}
     */
    backgroundColor: string;

    /**
     * CSS color string to represent the items draw onto the badge.
     * @type {String}
     */
    itemColor: string;

    /**
     * Desired font size in PTs.
     * @type {number}
     */
    fontSize: number;

    /**
     * Border radius of the badge background.
     * @type {number}
     */
    borderRadius: number;

    /**
     * Border radius of the badge background.
     * @type {number}
     */
    dottedRadius: number;

    /**
     * Border radius of the badge background.
     * @type {number}
     */
    innerRadius: number;

    /**
     * Z index order for this badge
     * @type {number}
     */
    zIndex: number;

    /**
     * Toggle to determine whether or not to animate the progress ring.
     * @type {boolean}
     */
    shouldAnimateProgressRing: boolean;

    /**
     * Time to take to animate the progress ring from 0 to 1 in milliseconds.
     * @type {number}
     */
    progressRingAnimationSpeed: number;

    /**
     * Should user event listeners for enter/leave of the badge (play/stop)?
     * @type {Boolean}
     */
    shouldAddEventListeners: boolean;

    /**
     * The effect type.
     */
    effectType: EffectTypeLiteral;

    /**
     * The playback style.
     */
    playbackStyle: PlaybackStyleLiteral;

    /**
     * The callback to invoke to play.
     */
    configurePlayAction: (callbackOrArg?: any) => void;

    /**
     * The callback to invoke to stop.
     */
    configureStopAction: (callbackOrArg?: any) => void;
}

export declare interface LivePhotoBadge extends LivePhotoBadgeProps {}
export declare class LivePhotoBadge implements LivePhotoBadgeProps {
    private _width;
    private _textMetrics;
    private _context;
    private _previousProgress;
    private _progress;
    private _shouldShowError;

    /**
     * The defaults for the values indicated in LivePhotoBadgeProps.
     */
    defaultProps: LivePhotoBadgeProps;

    /**
     * Create a new badge for the live photo.
     * @param opts Dictionary of options to set as instance props
     */
    constructor(opts?: Partial<LivePhotoBadgeProps>);

    /**
     * Calculated width of the badge.
     */
    readonly width: number;
    /**
     * String defining the font rendering.
     */
    readonly fontStyle: string;
    /**
     * Determine the horizontal center of the circles.
     */
    readonly x0: number;
    /**
     * Determine the vertical center of the circles.
     */
    readonly y0: number;
    /**
     * Number of dots to render in the outer circle.
     */
    static readonly numberOfDots: number;
    /**
     * Set/Get the progress value which will result in a progress ring being
     * drawn in the arc where the outer dots are. Value is 0 - 1, where 0 or 1
     * will reset the badge to the default state (dots on outer ring).
     */
    progress: number;
    /**
     * This shouldShowError determines whether the progress ring is instead
     * drawn as a slashed-out error circle (the universal "no" symbol).
     *
     * Assigning this value will redraw the badge.
     */
    shouldShowError: boolean;

    /**
     * Draw the badge, with either dots or progress ring.
     */
    redraw(): void;

    /**
     * Sugar for adding the badge to the DOM.
     * @param {HTMLElement} parentElement   Element in DOM to attach badge to
     */
    appendTo(parentElement: HTMLElement): void;

    private _createCanvas();

    private _setCanvasSize();

    private _setInstanceProps(opts);

    private _redraw(progress?);

    private _drawBackground();

    private _drawDottedCircle();

    private _drawDot(x, y);

    private _drawInnerCircle();

    private _drawPlayArrow();

    private _drawLabel();

    private _drawProgressRing(progress);

    private _drawErrorSlash();

    private _animateProgressRing();

    private _addEventListeners();
}
