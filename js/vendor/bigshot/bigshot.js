/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */
if (!self["bigshot"]) {
    /**
     * @namespace Bigshot namespace.
     *
     * Bigshot is a toolkit for zoomable images and VR panoramas.
     * 
     * <h3>Zoomable Images</h3>
     *
     * <p>The two classes that are needed for zoomable images are:
     *
     * <ul>
     * <li>{@link bigshot.Image}: The main class for making zoomable images. See the class docs
     *     for a tutorial.
     * <li>{@link bigshot.ImageParameters}: Parameters for zoomable images.
     * <li>{@link bigshot.SimpleImage}: A class for making simple zoomable images that don't
     * require the generation of an image pyramid.. See the class docs for a tutorial.
     * </ul>
     *
     * For hotspots, see:
     *
     * <ul>
     * <li>{@link bigshot.HotspotLayer}
     * <li>{@link bigshot.Hotspot}
     * <li>{@link bigshot.LabeledHotspot}
     * <li>{@link bigshot.LinkHotspot}
     * </ul>
     *
     * <h3>VR Panoramas</h3>
     *
     * <p>The two classes that are needed for zoomable VR panoramas (requires WebGL) are:
     *
     * <ul>
     * <li>{@link bigshot.VRPanorama}: The main class for making VR panoramas. See the class docs
     *     for a tutorial.
     * <li>{@link bigshot.VRPanoramaParameters}: Parameters for VR panoramas. 
     * </ul>
     *
     * For hotspots, see:
     *
     * <ul>
     * <li>{@link bigshot.VRHotspot}
     * <li>{@link bigshot.VRRectangleHotspot}
     * <li>{@link bigshot.VRPointHotspot}
     * </ul>
     */
    bigshot = {};
    
    /*
     * This is supposed to be processed using a minimalhttpd.IncludeProcessor
     * during development. The files must be listed in dependency order.
     */
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * This class has no constructor, it is created as an object literal.
 * @name bigshot.HomogeneousPoint3D
 * @class A 3d homogenous point. 
 * @property {number} x the x-coordinate
 * @property {number} y the y-coordinate
 * @property {number} z the z-coordinate
 * @property {number} w the w-coordinate
 */

/**
 * This class has no constructor, it is created as an object literal.
 * @name bigshot.Point3D
 * @class A 3d point. 
 * @property {number} x the x-coordinate
 * @property {number} y the y-coordinate
 * @property {number} z the z-coordinate
 */

/**
 * This class has no constructor, it is created as an object literal.
 * @name bigshot.Point2D
 * @class A 2d point. 
 * @property {number} x the x-coordinate
 * @property {number} y the y-coordinate
 */

/**
 * This class has no constructor, it is created as an object literal.
 * @name bigshot.Rotation
 * @class A rotation specified as a yaw-pitch-roll triplet. 
 * @property {number} y the rotation around the yaw (y) axis
 * @property {number} p the rotation around the pitch (x) axis
 * @property {number} r the rotation around the roll (z) axis
 */


/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * @class Object-oriented support functions, used to make JavaScript
 * a bit more palatable to a Java-head.
 */
bigshot.Object = {
    /**
     * Extends a base class with a derived class.
     *
     * @param {Function} derived the derived-class
     * @param {Function} base the base-class
     */
    extend : function (derived, base) {
        for (var k in base.prototype) {
            if (derived.prototype[k]) {
                derived.prototype[k]._super = base.prototype[k];
            } else {
                derived.prototype[k] = base.prototype[k];
            }
        }
    },
    
    /**
     * Resolves a name relative to <code>self</code>.
     *
     * @param {String} name the name to resolve
     * @type {Object}
     */
    resolve : function (name) {
        var c = name.split (".");
        var clazz = self;
        for (var i = 0; i < c.length; ++i) {
            clazz = clazz[c[i]];
        }
        return clazz;
    },
    
    validate : function (clazzName, iface) {
    },
    
    /**
     * Utility function to show an object's fields in a message box.
     *
     * @param {Object} o the object
     */
    alertr : function (o) {
        var sb = "";
        for (var k in o) {
            sb += k + ":" + o[k] + "\n";
        }
        alert (sb);
    },
    
    /**
     * Utility function to show an object's fields in the console log.
     *
     * @param {Object} o the object
     */
    logr : function (o) {
        var sb = "";
        for (var k in o) {
            sb += k + ":" + o[k] + "\n";
        }
        if (console) {
            console.log (sb);
        }
    }
};
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new browser helper object.
 *
 * @class Encapsulates common browser functions for cross-browser portability
 * and convenience.
 */
bigshot.Browser = function () {
    this.requestAnimationFrameFunction = 
        window.requestAnimationFrame || 
        window.mozRequestAnimationFrame ||  
        window.webkitRequestAnimationFrame || 
        window.msRequestAnimationFrame ||
        function (callback, element) { return setTimeout (callback, 0); };
}

bigshot.Browser.prototype = {
    /**
    * Removes all children from an element.
    * 
    * @public
    * @param {HTMLElement} element the element whose children are to be removed.
    */
    removeAllChildren : function (element) {
        element.innerHTML = "";
        /*
        if (element.children.length > 0) {
            for (var i = element.children.length - 1; i >= 0; --i) {
                element.removeChild (element.children[i]);
            }
        }
        */
    },
    
    /**
    * Thunk to implement a faked "mouseenter" event.
    * @private
    */
    mouseEnter : function (_fn) {
        var isAChildOf = this.isAChildOf;
        return function(_evt)
        {
            var relTarget = _evt.relatedTarget;
            if (this === relTarget || isAChildOf (this, relTarget))
            { return; }
            
            _fn.call (this, _evt);
        }
    },
    
    isAChildOf : function (_parent, _child) {
        if (_parent === _child) { return false; }
        while (_child && _child !== _parent)
        { _child = _child.parentNode; }
        
        return _child === _parent;
    },
    
    /**
    * Unregisters a listener from an element.
    *
    * @param {HTMLElement} elem the element
    * @param {String} eventName the event name ("click", "mouseover", etc.)
    * @param {function(e)} fn the callback function to detach
    * @param {boolean} useCapture specifies if we should unregister a listener from the capture chain.
    */
    unregisterListener : function (elem, eventName, fn, useCapture) {
        if (typeof (elem.removeEventListener) != 'undefined') {
            elem.removeEventListener (eventName, fn, useCapture);
        } else if (typeof (elem.detachEvent) != 'undefined') {
            elem.detachEvent('on' + eventName, fn);
        }
    },
    
    /**
    * Registers a listener to an element.
    *
    * @param {HTMLElement} elem the element
    * @param {String} eventName the event name ("click", "mouseover", etc.)
    * @param {function(e)} fn the callback function to attach
    * @param {boolean} useCapture specifies if we want to initiate capture.
    * See <a href="https://developer.mozilla.org/en/DOM/element.addEventListener">element.addEventListener</a>
    * on MDN for an explanation.
    */
    registerListener : function (_elem, _evtName, _fn, _useCapture) {
        if (typeof _elem.addEventListener != 'undefined')
        {
            if (_evtName === 'mouseenter')
            { _elem.addEventListener('mouseover', this.mouseEnter(_fn), _useCapture); }
            else if (_evtName === 'mouseleave')
            { _elem.addEventListener('mouseout', this.mouseEnter(_fn), _useCapture); }
            else
            { _elem.addEventListener(_evtName, _fn, _useCapture); }
        }
        else if (typeof _elem.attachEvent != 'undefined')
        {
            _elem.attachEvent('on' + _evtName, _fn);
        }
        else
        {
            _elem['on' + _evtName] = _fn;
        }
    },
    
    /**
    * Stops an event from bubbling.
    *
    * @param {Event} eventObject the event object
    */
    stopEventBubbling : function (eventObject) {
        if (eventObject) {
            if (eventObject.stopPropagation) {
                eventObject.stopPropagation ();
            } else { 
                eventObject.cancelBubble = true; 
            }
        }
    },
    
    /**
     * Creates a callback function that simply stops the event from bubbling.
     *
     * @example
     * var browser = new bigshot.Browser ();
     * browser.registerListener (element, 
     *     "mousedown", 
     *     browser.stopEventBubblingHandler (), 
     *     false);
     * @type function(event)
     * @return a new function that can be used to stop an event from bubbling
    */
    stopEventBubblingHandler : function () {
        var that = this;
        return function (event) {
            that.stopEventBubbling (event);
            return false;
        };
    },
    
    /**
     * Stops bubbling for all mouse events on the element.
     *
     * @param {HTMLElement} element the element
     */
    stopMouseEventBubbling : function (element) {
        this.registerListener (element, "mousedown", this.stopEventBubblingHandler (), false);
        this.registerListener (element, "mouseup", this.stopEventBubblingHandler (), false);
        this.registerListener (element, "mousemove", this.stopEventBubblingHandler (), false);
    },
    
    /**
     * Returns the size in pixels of the element
     *
     * @param {HTMLElement} obj the element
     * @return a size object with two integer members, w and h, for width and height respectively.
     */
    getElementSize : function (obj) {
        var size = {};
        if (obj.clientWidth) {
            size.w = obj.clientWidth;
        }
        if (obj.clientHeight) {
            size.h = obj.clientHeight;
        }
        return size;
    },
    
    /**
     * Returns true if the browser is scaling the window, such as on Mobile Safari.
     * The method used here is far from perfect, but it catches the most important use case:
     * If we are running on an iDevice and the page is zoomed out.
     */
    browserIsViewporting : function () {
        if (window.innerWidth <= screen.width) {
            return false;
        } else {
            return true;
        }
    },
    
    /**
     * Returns the device pixel scale, which is equal to the number of device 
     * pixels each css pixel corresponds to. Used to render the proper level of detail
     * on mobile devices, especially when zoomed out and more detailed textures are
     * simply wasted.
     *
     * @returns The number of device pixels each css pixel corresponds to.
     * For example, if the browser is zoomed out to 50% and a div with <code>width</code>
     * set to <code>100px</code> occupies 50 physical pixels, the function will return 
     * <code>0.5</code>.
     * @type number
     */
    getDevicePixelScale : function () {
        if (this.browserIsViewporting ()) {
            return screen.width / window.innerWidth;
        } else {
            return 1.0;
        }
    },
    
    /**
     * Requests an animation frame, if the API is supported
     * on the browser. If not, a <code>setTimeout</code> with 
     * a timeout of zero is used.
     *
     * @param {function()} callback the animation frame render function
     * @param {HTMLElement} element the element to use when requesting an
     * animation frame
     */
    requestAnimationFrame : function (callback, element) {
        var raff = this.requestAnimationFrameFunction;
        raff (callback, element);
    },
    
    /**
     * Returns the position in pixels of the element relative
     * to the top left corner of the document.
     *
     * @param {HTMLElement} obj the element
     * @return a position object with two integer members, x and y.
     */
    getElementPosition : function (obj) {
        var position = new Object();
        position.x = 0;
        position.y = 0;
        
        var o = obj;
        while (o) {
            position.x += o.offsetLeft;
            position.y += o.offsetTop;
            if (o.clientLeft) {
                position.x += o.clientLeft;
            }
            if (o.clientTop) {
                position.y += o.clientTop;
            }
            
            if (o.x) {
                position.x += o.x;
            }
            if (o.y) {
                position.y += o.y;
            }
            o = o.offsetParent;
        }
        return position;
    },
    
    /**
     * Creates an XMLHttpRequest object.
     *
     * @type XMLHttpRequest
     * @returns a XMLHttpRequest object.
     */
    createXMLHttpRequest : function  () {
        try { 
            return new ActiveXObject("Msxml2.XMLHTTP"); 
        } catch (e) {
        }
        
        try { 
            return new ActiveXObject("Microsoft.XMLHTTP"); 
        } catch (e) {
        }
        
        try { 
            return new XMLHttpRequest(); 
        } catch(e) {
        }
        
        alert("XMLHttpRequest not supported");
        
        return null;
    },
    
    /**
     * Creates an opacity transition from opaque to transparent.
     * If CSS transitions aren't supported, the element is
     * immediately made transparent without a transition.
     * 
     * @param {HTMLElement} element the element to fade out
     * @param {function()} onComplete function to call when
     * the transition is complete.
     */
    makeOpacityTransition : function (element, onComplete) {
        if (element.style.WebkitTransitionProperty != undefined) {
            element.style.opacity = 1.0;
            element.style.WebkitTransitionProperty = "opacity";
            element.style.WebkitTransitionTimingFunction = "linear";
            element.style.WebkitTransitionDuration = "1s";
            setTimeout (function () {
                element.addEventListener ("webkitTransitionEnd", function () {
                    onComplete ();
                });
                element.style.opacity = 0.0;
            }, 0);
        } else {
            element.style.opacity = 0.0;
            onComplete ();
        }
    }
};
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates an event dispatcher.
 *
 * @class Base class for objects that dispatch events.
 */
bigshot.EventDispatcher = function () {
    /**
     * The event listeners. Each key-value pair in the map is
     * an event name and an <code>Array</code> of listeners.
     * 
     * @type Object
     */
    this.eventListeners = {};
}

bigshot.EventDispatcher.prototype = {
    /**
     * Adds an event listener to the specified event.
     *
     * @example
     * image.addEventListener ("click", function (event) { ... });
     *
     * @param {String} eventName the name of the event to add a listener for
     * @param {Function} handler function that is invoked with an event object
     * when the event is fired
     */
    addEventListener : function (eventName, handler) {
        if (this.eventListeners[eventName] == undefined) {
            this.eventListeners[eventName] = new Array ();
        }
        this.eventListeners[eventName].push (handler);
    },
    
    /**
     * Removes an event listener.
     * @param {String} eventName the name of the event to remove a listener for
     * @param {Function} handler the handler to remove
     */
    removeEventListener : function (eventName, handler) {
        if (this.eventListeners[eventName] != undefined) {
            var el = this.eventListeners[eventName];
            for (var i = 0; i < el.length; ++i) {
                if (el[i] === listener) {
                    el.splice (i, 1);
                    if (el.length == 0) {
                        delete this.eventListeners[eventName];
                    }
                    break;
                }
            }
        }
    },
    
    /**
     * Fires an event.
     *
     * @param {String} eventName the name of the event to fire
     * @param {bigshot.Event} eventObject the event object to pass to the handlers
     */
    fireEvent : function (eventName, eventObject) {
        if (this.eventListeners[eventName] != undefined) {
            var el = this.eventListeners[eventName];
            for (var i = 0; i < el.length; ++i) {
                el[i](eventObject);
            }
        }
    }
};
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates an event.
 *
 * @class Base class for events. The interface is supposed to be as similar to
 * standard DOM events as possible.
 * @param {Object} data a data object whose fields will be used to set the 
 * corresponding fields of the event object.
 */
bigshot.Event = function (data) {

    /**
     * Indicates whether the event bubbles.
     * @default false
     * @type boolean
     */
    this.bubbles = false;
    
    /**
     * Indicates whether the event is cancelable.
     * @default false
     * @type boolean
     */
    this.cancelable = false;
    
    /**
     * The current target of the event
     * @default null
     */
    this.currentTarget = null;
    
    /**
     * Set if the preventDefault method has been called.
     * @default false
     * @type boolean
     */
    this.defaultPrevented = false;

    /**
     * The target to which the event is dispatched.
     * @default null
     */
    this.target = null;
    
    /**
     * The time the event was created, in milliseconds since the epoch.
     * @default the current time, as given by <code>new Date ().getTime ()</code>
     * @type number
     */
    this.timeStamp = new Date ().getTime ();
    
    /**
     * The event type.
     * @default null
     * @type String
     */
    this.type = null;
    
    /**
     * Flag indicating origin of event.
     * @default false
     * @type boolean
     */
    this.isTrusted = false;
    
    for (var k in data) {
        this[k] = data[k];
    }
}

bigshot.Event.prototype = {
    /**
     * Prevents default handling of the event.
     */
    preventDefault : function () {
        this.defaultPrevented = true;
    }
};
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */
    

/**
 * Creates a new instance of the cached resource. May return
 * null, in which case that value is cached. The function
 * may be called multiple times, but a corresponding call to
 * the dispose function will always occur inbetween.
 * @name bigshot.TimedWeakReference.Create
 * @function
 */

/**
 * Disposes a of the cached resource. 
 * @name bigshot.TimedWeakReference.Dispose
 * @function
 * @param {Object} resource the resource that was created
 * by the create function
 */

/**
 * Creates a new instance.
 *
 * @class Caches a lazy-created resource for a given time before
 * disposing it. 
 *
 * @param {bigshot.TimedWeakReference.Create} create a function that creates the
 * held resource. May be called multiple times, but not without a call to
 * dispose inbetween.
 * @param {bigshot.TimedWeakReference.Dispose} dispose a function that disposes the
 * resource created by create.
 * @param {int} interval the polling interval in milliseconds. If the last 
 * access time is further back than one interval, the held resource is 
 * disposed (and will be re-created
 * on the next call to get).
 */
bigshot.TimedWeakReference = function (create, dispose, interval) {
    this.object = null;
    this.hasObject = false;
    this.fnCreate = create;
    this.fnDispose = dispose;
    this.lastAccess = new Date ().getTime ();
    this.hasTimer = false;
    this.interval = interval;
};

bigshot.TimedWeakReference.prototype = {
    /**
     * Disposes of this instance. The resource is disposed.
     */
    dispose : function () {
        this.clear ();
    },
    
    /**
     * Gets the resource. The resource is created if needed.
     * The last access time is updated.
     */
    get : function () {
        if (!this.hasObject) {
            this.hasObject = true;
            this.object = this.fnCreate ();
            this.startTimer ();
        }
        this.lastAccess = new Date ().getTime ();
        return this.object;
    },
    
    /**
     * Forcibly disposes the held resource, if any.
     */
    clear : function () {
        if (this.hasObject) {
            this.hasObject = false;
            this.fnDispose (this.object);
            this.object = null;
            this.stopTimer ();
        }
    },
    
    /**
     * Stops the polling timer if it is running.
     * @private
     */
    stopTimer : function () {
        if (this.hasTimer) {
            clearTimeout (this.timerId);
            this.hasTimer = false;
        }
    },
    
    /**
     * Starts the polling timer if it isn't already running.
     * @private
     */
    startTimer : function () {
        if (!this.hasTimer) {
            var that = this;
            this.hasTimer = true;
            this.timerId = setTimeout (function () {
                    that.hasTimer = false;
                    that.update ();
                }, this.interval);
        }
    },
    
    /**
     * Disposes of the held resource if it hasn't been
     * accessed in {@link #interval} milliseconds.
     * @private
     */
    update : function () {
        if (this.hasObject) {
            var now = new Date ().getTime ();
            if (now - this.lastAccess > this.interval) {
                this.clear ();
            } else {
                this.startTimer ();
            }
        }
    }
}

/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates an image event.
 *
 * @class Base class for events dispatched by bigshot.ImageBase.
 * @param {Object} data a data object whose fields will be used to set the 
 * corresponding fields of the event object.
 * @extends bigshot.Event
 * @see bigshot.ImageBase
 */
bigshot.ImageEvent = function (data) {
    bigshot.Event.call (this, data);
}

/**
 * The image X coordinate of the event, if any.
 *
 * @name bigshot.ImageEvent#imageX
 * @field
 * @type number
 */

/**
 * The image Y coordinate of the event, if any.
 *
 * @name bigshot.ImageEvent#imageY
 * @field
 * @type number
 */

/**
 * The client X coordinate of the event, if any.
 *
 * @name bigshot.ImageEvent#clientX
 * @field
 * @type number
 */

/**
 * The client Y coordinate of the event, if any.
 *
 * @name bigshot.ImageEvent#clientY
 * @field
 * @type number
 */

/**
 * The local X coordinate of the event, if any.
 *
 * @name bigshot.ImageEvent#localX
 * @field
 * @type number
 */

/**
 * The local Y coordinate of the event, if any.
 *
 * @name bigshot.ImageEvent#localY
 * @field
 * @type number
 */


bigshot.ImageEvent.prototype = {
};

bigshot.Object.extend (bigshot.ImageEvent, bigshot.Event);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates an image event.
 *
 * @class Base class for events dispatched by bigshot.VRPanorama.
 * @param {Object} data a data object whose fields will be used to set the 
 * corresponding fields of the event object.
 * @extends bigshot.Event
 * @see bigshot.VRPanorama
 */
bigshot.VREvent = function (data) {
    bigshot.Event.call (this, data);
}

/**
 * The yaw coordinate of the event, if any.
 *
 * @name bigshot.VREvent#yaw
 * @field
 * @type number
 */

/**
 * The pitch coordinate of the event, if any.
 *
 * @name bigshot.VREvent#pitch
 * @field
 * @type number
 */

/**
 * The client X coordinate of the event, if any.
 *
 * @name bigshot.VREvent#clientX
 * @field
 * @type number
 */

/**
 * The client Y coordinate of the event, if any.
 *
 * @name bigshot.VREvent#clientY
 * @field
 * @type number
 */

/**
 * The local X coordinate of the event, if any.
 *
 * @name bigshot.VREvent#localX
 * @field
 * @type number
 */

/**
 * The local Y coordinate of the event, if any.
 *
 * @name bigshot.VREvent#localY
 * @field
 * @type number
 */

/**
 * A x,y,z triplet specifying a 3D ray from the viewer in the direction the 
 * event took place. The same as the yaw and pitch fields, but in Cartesian 
 * coordinates.
 *
 * @name bigshot.VREvent#ray
 * @field
 * @type xyz-triplet
 */


bigshot.VREvent.prototype = {
};

bigshot.Object.extend (bigshot.VREvent, bigshot.Event);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new full-screen handler for an element.
 * 
 * @class A utility class for making an element "full screen", or as close to that
 * as browser security allows. If the browser supports the <code>requestFullScreen</code>
 * API - as standard or as <code>moz</code>- or <code>webkit</code>- extensions,
 * this will be used.
 *
 * @param {HTMLElement} container the element that is to be made full screen
 */
bigshot.FullScreen = function (container) {
    this.container = container;
    
    this.isFullScreen = false;
    this.savedBodyStyle = null;
    this.savedParent = null;
    this.savedSize = null;
    this.expanderDiv = null;
    this.restoreSize = false;
    
    this.onCloseHandlers = new Array ();
    this.onResizeHandlers = new Array ();
    
    var findFunc = function (el, list) {
        for (var i = 0; i < list.length; ++i) {
            if (el[list[i]]) {
                return list[i];
            }
        }
        return null;
    };
    
    this.requestFullScreen = findFunc (container, ["requestFullScreen", "mozRequestFullScreen", "webkitRequestFullScreen"]);
    this.cancelFullScreen = findFunc (document, ["cancelFullScreen", "mozCancelFullScreen", "webkitCancelFullScreen"]);
    
    this.restoreSize = this.requestFullScreen != null;
}

bigshot.FullScreen.prototype = {
    browser : new bigshot.Browser (),
    
    getRootElement : function () {
        return this.div;
    },
    
    /**
     * Adds a function that will run when exiting full screen mode.
     *
     * @param {function()} onClose the function to call
     */
    addOnClose : function (onClose) {
        this.onCloseHandlers.push (onClose);
    },
    
    /**
     * Notifies all <code>onClose</code> handlers.
     *
     * @private
     */
    onClose : function () {
        for (var i = 0; i < this.onCloseHandlers.length; ++i) {
            this.onCloseHandlers[i] ();
        }
    },
    
    /**
     * Adds a function that will run when the element is resized.
     *
     * @param {function()} onResize the function to call
     */
    addOnResize : function (onResize) {
        this.onResizeHandlers.push (onResize);
    },
    
    /**
     * Notifies all resize handlers.
     *
     * @private
     */
    onResize : function () {
        for (var i = 0; i < this.onResizeHandlers.length; ++i) {
            this.onResizeHandlers[i] ();
        }
    },
    
    /**
     * Begins full screen mode.
     */
    open : function () {
        this.isFullScreen = true;
        
        if (this.requestFullScreen) {
            return this.openRequestFullScreen ();
        } else {
            return this.openCompat ();
        }
    },
    
    /**
     * Makes the element really full screen using the <code>requestFullScreen</code>
     * API.
     *
     * @private
     */
    openRequestFullScreen : function () {
        this.savedSize = {
            width : this.container.style.width,
            height : this.container.style.height
        };
        
        this.container.style.width = "100%";
        this.container.style.height = "100%";
        
        var that = this;
        
        if (this.requestFullScreen == "mozRequestFullScreen") {
            /**
             * @private
             */
            var errFun = function () {
                that.container.removeEventListener ("mozfullscreenerror", errFun);
                that.isFullScreen = false;
                that.exitFullScreenHandler ();
                that.onClose ();
            };
            this.container.addEventListener ("mozfullscreenerror", errFun);
            
            /**
             * @private
             */
            var changeFun = function () {
                if (document.mozFullScreenElement !== that.container) {
                    document.removeEventListener ("mozfullscreenchange", changeFun);
                    that.exitFullScreenHandler ();
                } else {
                    that.onResize ();
                }
            };
            document.addEventListener ("mozfullscreenchange", changeFun);
        } else {
            /**
             * @private
             */
            var changeFun = function () {
                if (document.webkitCurrentFullScreenElement !== that.container) {
                    that.container.removeEventListener ("webkitfullscreenchange", changeFun);
                    that.exitFullScreenHandler ();
                } else {
                    that.onResize ();
                }
            };
            this.container.addEventListener ("webkitfullscreenchange", changeFun);
        }
        
        this.exitFullScreenHandler = function () {
            if (that.isFullScreen) {
                that.isFullScreen = false;
                document[that.cancelFullScreen]();
                if (that.restoreSize) {
                    that.container.style.width = that.savedSize.width;
                    that.container.style.height = that.savedSize.height;
                }
                that.onResize ();
                that.onClose ();
            }
        };
        this.container[this.requestFullScreen]();
    },
    
    /**
     * Makes the element "full screen" in older browsers by covering the browser's client area.
     * 
     * @private
     */
    openCompat : function () {
        this.savedParent = this.container.parentNode;
        
        this.savedSize = {
            width : this.container.style.width,
            height : this.container.style.height
        };
        this.savedBodyStyle = document.body.style.cssText;
        
        document.body.style.overflow = "hidden";
        
        this.expanderDiv = document.createElement ("div");
        this.expanderDiv.style.position = "absolute";
        this.expanderDiv.style.top = "0px";
        this.expanderDiv.style.left = "0px";
        this.expanderDiv.style.width = Math.max (window.innerWidth, document.documentElement.clientWidth) + "px";
        this.expanderDiv.style.height = Math.max (window.innerHeight, document.documentElement.clientHeight) + "px";
        
        document.body.appendChild (this.expanderDiv);
        
        this.div = document.createElement ("div");
        this.div.style.position = "fixed";
        this.div.style.top = window.pageYOffset + "px";
        this.div.style.left = window.pageXOffset + "px";
        
        this.div.style.width = window.innerWidth + "px";
        this.div.style.height = window.innerHeight + "px";
        this.div.style.zIndex = 9998;
        
        this.div.appendChild (this.container);
        
        //this.container.style.width = window.innerWidth + "px";
        //this.container.style.height = window.innerHeight + "px";
        
        document.body.appendChild (this.div);
        
        var that = this;
        var resizeHandler = function (e) {
            setTimeout (function () {
                    that.div.style.width = window.innerWidth + "px";
                    that.div.style.height = window.innerHeight + "px";                    
                    setTimeout (function () {
                            that.onResize ();
                        }, 1);
                }, 1);
        };
        
        
        var rotationHandler = function (e) {
            that.expanderDiv.style.width = Math.max (window.innerWidth, document.documentElement.clientWidth) + "px";
            that.expanderDiv.style.height = Math.max (window.innerHeight, document.documentElement.clientHeight) + "px";
            setTimeout (function () {
                    that.div.style.top = window.pageYOffset + "px";
                    that.div.style.left = window.pageXOffset + "px";
                    that.div.style.width = window.innerWidth + "px";
                    that.div.style.height = window.innerHeight + "px";
                    setTimeout (function () {
                            that.onResize ();
                        }, 1);
                }, 1);
        };
        
        var escHandler = function (e) {
            if (e.keyCode == 27) {
                that.exitFullScreenHandler ();
            }
        };
        
        this.exitFullScreenHandler = function () {
            that.isFullScreen = false;
            that.browser.unregisterListener (document, "keydown", escHandler);
            that.browser.unregisterListener (window, "resize", resizeHandler);
            that.browser.unregisterListener (document.body, "orientationchange", rotationHandler);
            if (that.restoreSize) {
                that.container.style.width = that.savedSize.width;
                that.container.style.height = that.savedSize.height;
            }     
            
            document.body.style.cssText = that.savedBodyStyle;
            
            that.savedParent.appendChild (that.container);
            document.body.removeChild (that.div);
            document.body.removeChild (that.expanderDiv);
            
            that.onResize ();            
            that.onClose ();
            setTimeout (function () {
                    that.onResize ();
                }, 1);
        };
        
        this.browser.registerListener (document, "keydown", escHandler, false);
        this.browser.registerListener (window, "resize", resizeHandler, false);
        this.browser.registerListener (document.body, "orientationchange", rotationHandler, false);
        
        this.onResize ();
        
        return this.exitFullScreenHandler;
    },
    
    /**
     * Ends full screen mode.
     */
    close : function () {
        this.exitFullScreenHandler ();
    }
};

/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */
    
/**
 * @class Loads image and XML data.
 */
bigshot.DataLoader = function () {
}

bigshot.DataLoader.prototype = {
    /**
     * Loads an image.
     *
     * @param {String} url the url to load
     * @param {function(success,img)} onloaded called on complete 
     */
    loadImage : function (url, onloaded) {},
    
    /**
     * Loads XML data.
     *
     * @param {String} url the url to load
     * @param {boolean} async use async request
     * @param {function(success,xml)} [onloaded] called on complete for async requests
     * @return the xml for synchronous calls
     */
    loadXml : function (url, async, onloaded) {}
}
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new data loader.
 *
 * @param {int} [maxRetries=0] the maximum number of times to retry requests
 * @param {String} [crossOrigin] the CORS crossOrigin parameter to use when loading images
 * @class Data loader using standard browser functions.
 * @augments bigshot.DataLoader
 */
bigshot.DefaultDataLoader = function (maxRetries, crossOrigin) {
    this.maxRetries = maxRetries;
    this.crossOrigin = crossOrigin;
    
    if (!this.maxRetries) {
        this.maxRetries = 0;
    }    
}

bigshot.DefaultDataLoader.prototype = {
    browser : new bigshot.Browser (),
    
    loadImage : function (url, onloaded) {
        var tile = document.createElement ("img");
        tile.retries = 0;
        if (this.crossOrigin != null) {
            tile.crossOrigin = this.crossOrigin;
        }
        var that = this;
        this.browser.registerListener (tile, "load", function () {
                if (onloaded) {
                    onloaded (tile);
                }
            }, false);
        this.browser.registerListener (tile, "error", function () {
                tile.retries++;
                if (tile.retries <= that.maxRetries) {
                    setTimeout (function () {
                            tile.src = url;
                        }, tile.retries * 1000);
                } else {
                    if (onloaded) {
                        onloaded (null);
                    }
                }
            }, false);
        tile.src = url;
        return tile;
    },
    
    loadXml : function (url, synchronous, onloaded) {
        for (var tries = 0; tries <= this.maxRetries; ++tries) {
            var req = this.browser.createXMLHttpRequest ();
            
            req.open("GET", url, false);   
            req.send(null); 
            if(req.status == 200) {
                var xml = req.responseXML;
                if (xml != null) {
                    if (onloaded) {
                        onloaded (xml);
                    }
                    return xml;
                }
            } 
            
            if (tries == that.maxRetries) {
                if (onloaded) {
                    onloaded (null);
                }
                return null;
            }
        }
    }
}

bigshot.Object.validate ("bigshot.DefaultDataLoader", bigshot.DataLoader);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * @class Data loader using standard browser functions that maintains
 * an in-memory cache of everything loaded.
 * @augments bigshot.DataLoader
 */
bigshot.CachingDataLoader = function () {
    this.cache = {};
    this.requested = {};
    this.requestedTiles = {};
}

bigshot.CachingDataLoader.prototype = {
    
    browser : new bigshot.Browser (),
    
    loadImage : function (url, onloaded) {
        if (this.cache[url]) {
            if (onloaded) {
                onloaded (this.cache[url]);
            }
            return this.cache[url];
        } else if (this.requested[url]) {
            if (onloaded) {
                this.requested[url].push (onloaded);
            }
            return this.requestedTiles[url];
        } else {
            var that = this;
            this.requested[url] = new Array ();
            if (onloaded) {
                this.requested[url].push (onloaded);
            }
            
            var tile = document.createElement ("img");
            this.requestedTiles[url] = tile;
            this.browser.registerListener (tile, "load", function () {                        
                    var listeners = that.requested[url];
                    delete that.requested[url];
                    delete that.requestedTiles[url];
                    that.cache[url] = tile;
                    
                    for (var i = 0; i < listeners.length; ++i) {
                        listeners[i] (tile);
                    }
                }, false);
            tile.src = url;
            return tile;
        }
    },
    
    loadXml : function (url, async, onloaded) {
        if (this.cache[url]) {
            if (onloaded) {
                onloaded (this.cache[url]);
            }
            return this.cache[url];
        } else if (this.requested[url] && async) {
            if (onloaded) {
                this.requested[url].push (onloaded);
            }
        } else {
            var req = this.browser.createXMLHttpRequest ();
            
            if (!this.requested[url]) {
                this.requested[url] = new Array ();
            }
            
            if (async) {
                if (onloaded) {
                    this.requested[url].push (onloaded);
                }
            }
            
            var that = this;
            var finishRequest = function () {
                if (that.requested[url]) {
                    var xml = null;
                    if(req.status == 200) {
                        xml = req.responseXML;
                    }
                    var listeners = that.requested[url];
                    delete that.requested[url];
                    that.cache[url] = xml
                    
                    for (var i = 0; i < listeners.length; ++i) {
                        listeners[i](xml);
                    }
                }
                return xml;
            };
            
            if (async) {
                req.onreadystatechange = function () {
                    if (req.readyState == 4) {
                        finishRequest ();
                    }
                };
                req.open("GET", url, true);
                req.send ();
            } else {
                req.open("GET", url, false);
                req.send ();
                return finishRequest ();                
            }
        }
    }
}

bigshot.Object.validate ("bigshot.CachingDataLoader", bigshot.DataLoader);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new hotspot instance.
 *
 * @class Base class for hotspots in a {@link bigshot.HotspotLayer}. See {@link bigshot.HotspotLayer} for 
 * examples.
 *
 * @param {number} x x-coordinate of the top-left corner, given in full image pixels
 * @param {number} y y-coordinate of the top-left corner, given in full image pixels
 * @param {number} w width of the hotspot, given in full image pixels
 * @param {number} h height of the hotspot, given in full image pixels
 * @see bigshot.HotspotLayer
 * @see bigshot.LabeledHotspot
 * @see bigshot.LinkHotspot
 * @constructor
 */
bigshot.Hotspot = function (x, y, w, h) {
    var element = document.createElement ("div");
    element.style.position = "absolute";
    element.style.overflow = "visible";
    
    this.element = element;
    this.x = x;
    this.y = y;
    this.w = w;
    this.h = h;
}

bigshot.Hotspot.prototype = {
    
    browser : new bigshot.Browser (),
    
    /**
     * Lays out the hotspot in the viewport.
     *
     * @name bigshot.Hotspot#layout
     * @param x0 x-coordinate of top-left corner of the full image in css pixels
     * @param y0 y-coordinate of top-left corner of the full image in css pixels
     * @param zoomFactor the zoom factor.
     * @function
     */
    layout : function (x0, y0, zoomFactor) {
        var sx = this.x * zoomFactor + x0;
        var sy = this.y * zoomFactor + y0;
        var sw = this.w * zoomFactor;
        var sh = this.h * zoomFactor;
        this.element.style.top = sy + "px";
        this.element.style.left = sx + "px";
        this.element.style.width = sw + "px";
        this.element.style.height = sh + "px";
    },
    
    /**
     * Returns the HTMLDivElement used to show the hotspot.
     * Clients can access this element in order to style it.
     *
     * @type HTMLDivElement
     */
    getElement : function () {
        return this.element;
    }
};
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new labeled hotspot instance.
 *
 * @class A point hotspot consisting of an image.
 *
 * @see bigshot.HotspotLayer
 * @param {number} x x-coordinate of the center corner, given in full image pixels
 * @param {number} y y-coordinate of the center corner, given in full image pixels
 * @param {number} w width of the hotspot, given in screen pixels
 * @param {number} h height of the hotspot, given in screen pixels
 * @param {number} xo x-offset, given in screen pixels
 * @param {number} yo y-offset, given in screen pixels
 * @param {HTMLElement} element the HTML element to position
 * @param {String} [imageUrl] the image to use as hotspot sprite
 * @augments bigshot.Hotspot
 */
bigshot.PointHotspot = function (x, y, w, h, xo, yo, imageUrl) {
    bigshot.Hotspot.call (this, x, y, w, h);
    this.xo = xo;
    this.yo = yo;
    
    if (imageUrl) {
        var el = this.getElement ();
        el.style.backgroundImage = "url('" + imageUrl + "')";
        el.style.backgroundRepeat = "no-repeat";
    }
}

bigshot.PointHotspot.prototype = {
    /**
     * Returns the label element.
     *
     * @type HTMLDivElement
     */
    getLabel : function () {
        return this.label;
    },
    
    layout : function (x0, y0, zoomFactor) {
        var sx = this.x * zoomFactor + x0 + this.xo;
        var sy = this.y * zoomFactor + y0 + this.yo;
        this.element.style.top = sy + "px";
        this.element.style.left = sx + "px";
        this.element.style.width = this.w + "px";
        this.element.style.height = this.h + "px";
    }
};

bigshot.Object.extend (bigshot.PointHotspot, bigshot.Hotspot);

/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Abstract interface description for a Layer.
 *
 * @class Abstract interface description for a layer.
 */
bigshot.Layer = function () {
}

bigshot.Layer.prototype = {
    /**
     * Returns the layer container.
     *
     * @type HTMLDivElement
     */
    getContainer : function () {},
    
    /**
     * Sets the maximum number of image tiles that will be visible in the image.
     *
     * @param {int} x the number of tiles horizontally
     * @param {int} y the number of tiles vertically
     */
    setMaxTiles : function (x, y) {},
    
    /**
     * Called when the image's viewport is resized.
     *
     * @param {int} w the new width of the viewport, in css pixels
     * @param {int} h the new height of the viewport, in css pixels
     */
    resize : function (w, h) {},
    
    /**
     * Lays out the layer.
     *
     * @param {number} zoom the zoom level, adjusted for texture stretching
     * @param {number} x0 the x-coordinate of the top-left corner of the top-left tile in css pixels
     * @param {number} y0 the y-coordinate of the top-left corner of the top-left tile in css pixels
     * @param {number} tx0 column number (starting at zero) of the top-left tile
     * @param {number} ty0 row number (starting at zero) of the top-left tile
     * @param {number} size the {@link bigshot.ImageParameters#tileSize} - width of each 
     *                 image tile in pixels - of the image
     * @param {number} stride offset (vertical and horizontal) from the top-left corner
     *                 of a tile to the next tile's top-left corner.
     * @param {number} opacity the opacity of the layer as a CSS opacity value.
     */
    layout : function (zoom, x0, y0, tx0, ty0, size, stride, opacity) {}
};
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */
    
/**
 * Creates a new labeled hotspot instance.
 *
 * @class A hotspot with a label under it. The label element can be accessed using
 * the getLabel method and styled as any HTMLElement. See {@link bigshot.HotspotLayer} for 
 * examples.
 *
 * @see bigshot.HotspotLayer
 * @param {number} x x-coordinate of the top-left corner, given in full image pixels
 * @param {number} y y-coordinate of the top-left corner, given in full image pixels
 * @param {number} w width of the hotspot, given in full image pixels
 * @param {number} h height of the hotspot, given in full image pixels
 * @param {String} labelText text of the label
 * @augments bigshot.Hotspot
 */
bigshot.LabeledHotspot = function (x, y, w, h, labelText) {
    bigshot.Hotspot.call (this, x, y, w, h);
    
    this.label = document.createElement ("div");
    this.label.style.position = "relative";
    this.label.style.display = "inline-block";
    
    this.getElement ().appendChild (this.label);
    this.label.innerHTML = labelText;
    this.labelSize = this.browser.getElementSize (this.label);
}

bigshot.LabeledHotspot.prototype = {
    /**
     * Returns the label element.
     *
     * @type HTMLDivElement
     */
    getLabel : function () {
        return this.label;
    },
    
    layout : function (x0, y0, zoomFactor) {
        this.layout._super.call (this, x0, y0, zoomFactor);
        var sw = this.w * zoomFactor;
        var sh = this.h * zoomFactor;
        this.label.style.top = (sh + 4) + "px";
        this.label.style.left = ((sw - this.labelSize.w) / 2) + "px";
    }
};

bigshot.Object.extend (bigshot.LabeledHotspot, bigshot.Hotspot);

/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */
    
/**
 * Creates a new link-hotspot instance.
 *
 * @class A labeled hotspot that takes the user to another
 * location when it is clicked on. See {@link bigshot.HotspotLayer} for 
 * examples.
 *
 * @see bigshot.HotspotLayer
 * @param {number} x x-coordinate of the top-left corner, given in full image pixels
 * @param {number} y y-coordinate of the top-left corner, given in full image pixels
 * @param {number} w width of the hotspot, given in full image pixels
 * @param {number} h height of the hotspot, given in full image pixels
 * @param {String} labelText text of the label
 * @param {String} url url to go to on click
 * @augments bigshot.LabeledHotspot
 * @constructor
 */
bigshot.LinkHotspot = function (x, y, w, h, labelText, url) {
    bigshot.LabeledHotspot.call (this, x, y, w, h, labelText);
    this.browser.registerListener (this.getElement (), "click", function () {
            document.location.href = url;
        });
};

bigshot.Object.extend (bigshot.LinkHotspot, bigshot.LabeledHotspot);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new hotspot layer. The layer must be added to the image using
 * {@link bigshot.ImageBase#addLayer}.
 *
 * @class A hotspot layer.
 * @example
 * var image = new bigshot.Image (...);
 * var hotspotLayer = new bigshot.HotspotLayer (image);
 * var hotspot = new bigshot.LinkHotspot (100, 100, 200, 100, 
 *    "Bigshot on Google Code", 
 *    "http://code.google.com/p/bigshot/");
 *
 * // Style the hotspot a bit
 * hotspot.getElement ().className = "hotspot"; 
 * hotspot.getLabel ().className = "label";
 *
 * hotspotLayer.addHotspot (hotspot);
 *
 * image.addLayer (hotspotLayer);
 * 
 * @param {bigshot.ImageBase} image the image this hotspot layer will be part of
 * @augments bigshot.Layer
 * @constructor
 */
bigshot.HotspotLayer = function (image) {
    this.image = image;
    this.hotspots = new Array ();
    this.browser = new bigshot.Browser ();
    this.container = image.createLayerContainer ();
    this.parentContainer = image.getContainer ();
    this.resize (0, 0);
}

bigshot.HotspotLayer.prototype = {
    
    getContainer : function () {
        return this.container;
    },
    
    resize : function (w, h) {
        this.container.style.width = this.parentContainer.clientWidth + "px";
        this.container.style.height = this.parentContainer.clientHeight + "px";
    },
    
    layout : function (zoom, x0, y0, tx0, ty0, size, stride, opacity) {
        var zoomFactor = Math.pow (2, this.image.getZoom ());
        x0 -= stride * tx0;
        y0 -= stride * ty0;
        for (var i = 0; i < this.hotspots.length; ++i) {
            this.hotspots[i].layout (x0, y0, zoomFactor);
        }            
    },
    
    setMaxTiles : function (mtx, mty) {
    },
    
    /**
     * Adds a hotspot to the layer. 
     *
     * @param {bigshot.Hotspot} hotspot the hotspot to add.
     */
    addHotspot : function (hotspot) {
        this.container.appendChild (hotspot.getElement ());
        this.hotspots.push (hotspot);
    }
}

bigshot.Object.validate ("bigshot.HotspotLayer", bigshot.Layer);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new image layer.
 * 
 * @param {bigshot.ImageBase} image the image that this layer is part of
 * @param {bigshot.ImageParameters} parameters the associated image parameters
 * @param {number} w the current width in css pixels of the viewport
 * @param {number} h the current height in css pixels of the viewport
 * @param {bigshot.ImageTileCache} itc the tile cache to use
 * @class A tiled, zoomable image layer.
 * @constructor
 */
bigshot.TileLayer = function (image, parameters, w, h, itc) {
    this.rows = new Array ();
    this.browser = new bigshot.Browser ();
    this.container = image.createLayerContainer ();
    this.parentContainer = image.getContainer ();
    this.parameters = parameters;
    this.w = w;
    this.h = h;
    this.imageTileCache = itc;
    
    this.resize (w, h);
    return this;
}

bigshot.TileLayer.prototype = {
    getContainer : function () {
        return this.container;
    },
    
    resize : function (w, h) {
        this.container.style.width = this.parentContainer.clientWidth + "px";
        this.container.style.height = this.parentContainer.clientHeight + "px";
        this.pixelWidth = this.parentContainer.clientWidth;
        this.pixelHeight = this.parentContainer.clientHeight;
        this.w = w;
        this.h = h;
        this.rows = new Array ();
        this.browser.removeAllChildren (this.container);
        for (var r = 0; r < h; ++r) {
            var row = new Array ();
            for (var c = 0; c < w; ++c) {
                var tileAnchor = document.createElement ("div");
                tileAnchor.style.position = "absolute";
                tileAnchor.style.overflow = "hidden";
                tileAnchor.style.width = this.container.clientWidth + "px";
                tileAnchor.style.height = this.container.clientHeight + "px";
                
                var tile = document.createElement ("div");
                tile.style.position = "relative";
                tile.style.border = "hidden";
                tile.style.visibility = "hidden";
                tile.bigshotData = {
                    visible : false
                };
                row.push (tile);
                this.container.appendChild (tileAnchor);
                tileAnchor.appendChild (tile);
            }
            this.rows.push (row);
        }
    },
    
    layout : function (zoom, x0, y0, tx0, ty0, size, stride, opacity) {
        zoom = Math.min (0, Math.ceil (zoom));

        this.imageTileCache.resetUsed ();
        var y = y0;
        
        var visible = 0;
        for (var r = 0; r < this.h; ++r) {
            var x = x0;
            for (var c = 0; c < this.w; ++c) {
                var tile = this.rows[r][c];
                var bigshotData = tile.bigshotData;
                if (x + size < 0 || x > this.pixelWidth || y + size < 0 || y > this.pixelHeight) {
                    if (bigshotData.visible) {
                        bigshotData.visible = false;
                        tile.style.visibility = "hidden";
                    }
                } else {
                    visible++;
                    tile.style.left = x + "px";
                    tile.style.top = y + "px";
                    tile.style.width = size + "px";
                    tile.style.height = size + "px";
                    tile.style.opacity = opacity;
                    if (!bigshotData.visible) {
                        bigshotData.visible = true;
                        tile.style.visibility = "visible";
                    }
                    var tx = c + tx0;
                    var ty = r + ty0;
                    if (this.parameters.wrapX) {
                        if (tx < 0 || tx >= this.imageTileCache.maxTileX) {
                            tx = (tx + this.imageTileCache.maxTileX) % this.imageTileCache.maxTileX;
                        }
                    }
                    
                    if (this.parameters.wrapY) {
                        if (ty < 0 || ty >= this.imageTileCache.maxTileY) {
                            ty = (ty + this.imageTileCache.maxTileY) % this.imageTileCache.maxTileY;
                        }
                    }
                    
                    var imageKey = tx + "_" + ty + "_" + zoom;
                    var isOutside = tx < 0 || tx >= this.imageTileCache.maxTileX || ty < 0 || ty >= this.imageTileCache.maxTileY;
                    if (isOutside) {
                        if (!bigshotData.isOutside) {
                            var image = this.imageTileCache.getImage (tx, ty, zoom);
                            
                            this.browser.removeAllChildren (tile);
                            tile.appendChild (image);
                            bigshotData.image = image;
                        }
                        bigshotData.isOutside = true;
                        bigshotData.imageKey = "EMPTY";
                        bigshotData.image.style.width = size + "px";
                        bigshotData.image.style.height = size + "px";                            
                    } else {
                        var image = this.imageTileCache.getImage (tx, ty, zoom);
                        
                        bigshotData.isOutside = false;
                        
                        if (bigshotData.imageKey !== imageKey || bigshotData.isPartial) {
                            this.browser.removeAllChildren (tile);
                            tile.appendChild (image);
                            bigshotData.image = image;
                            bigshotData.imageKey = imageKey;     
                            bigshotData.isPartial = image.isPartial;
                        }
                        bigshotData.image.style.width = size + "px";
                        bigshotData.image.style.height = size + "px";
                        
                    }                    
                }
                x += stride;
            }
            y += stride;
        }
    },
    
    setMaxTiles : function (mtx, mty) {
        this.imageTileCache.setMaxTiles (mtx, mty);
    }
};

bigshot.Object.validate ("bigshot.TileLayer", bigshot.Layer);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new, empty, LRUMap instance.
 * 
 * @class Implementation of a Least-Recently-Used cache map.
 * Used by the ImageTileCache to keep track of cache entries.
 * @constructor
 */
bigshot.LRUMap = function () {
    /** 
     * Key to last-accessed time mapping.
     *
     * @type Object
     */
    this.keyToTime = {};
    
    /**
     * Current time counter. Incremented for each access of
     * a key in the map.
     * @type int
     */
    this.counter = 0;
    
    /** 
     * Current size of the map.
     * @type int
     */
    this.size = 0;
}

bigshot.LRUMap.prototype = {
    /**
     * Marks access to an item, represented by its key in the map. 
     * The key's last-accessed time is updated to the current time
     * and the current time is incremented by one step.
     *
     * @param {String} key the key associated with the accessed item
     */
    access : function (key) {
        this.remove (key);
        this.keyToTime[key] = this.counter;
        ++this.counter;
        ++this.size;
    },
    
    /**
     * Removes a key from the map.
     *
     * @param {String} key the key to remove
     * @returns true iff the key existed in the map.
     * @type boolean
     */
    remove : function (key) {
        if (this.keyToTime[key]) {
            delete this.keyToTime[key];
            --this.size;
            return true;
        } else {
            return false;
        }
    },
    
    /**
     * Returns the current number of keys in the map.
     * @type int
     */
    getSize : function () {
        return this.size;
    },
    
    /**
     * Returns the key in the map with the lowest
     * last-accessed time. This is done as a linear
     * search through the map. It could be done much 
     * faster with a sorted map, but unless this becomes
     * a bottleneck it is just not worth the effort.
     * @type String
     */
    leastUsed : function () {
        var least = this.counter + 1;
        var leastKey = null;
        for (var k in this.keyToTime) {
            if (this.keyToTime[k] < least) {
                least = this.keyToTime[k];
                leastKey = k;
            }
        }
        return leastKey;
    }
};
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new cache instance.
 *
 * @class Tile cache for the {@link bigshot.TileLayer}.
 * @constructor
 */
bigshot.ImageTileCache = function (onLoaded, onCacheInit, parameters) {
    var that = this;
    
    this.parameters = parameters;
    
    /**
     * Reduced-resolution preview of the full image.
     * Loaded from the "poster" image created by 
     * MakeImagePyramid
     *
     * @private
     * @type HTMLImageElement
     */
    this.fullImage = null;
    parameters.dataLoader.loadImage (parameters.fileSystem.getPosterFilename (), function (tile) {
            that.fullImage = tile;
            if (onCacheInit) {
                onCacheInit ();
            }
        });
    
    /**
     * Maximum number of tiles in the cache.
     * @private
     * @type int
     */
    this.maxCacheSize = 512;
    this.maxTileX = 0;
    this.maxTileY = 0;
    this.cachedImages = {};
    this.requestedImages = {};
    this.usedImages = {};
    this.lastOnLoadFiredAt = 0;
    this.imageRequests = 0;
    this.lruMap = new bigshot.LRUMap ();
    this.onLoaded = onLoaded;
    this.browser = new bigshot.Browser ();
    this.partialImageSize = parameters.tileSize / 4;
    this.POSTER_ZOOM_LEVEL = Math.log (parameters.posterSize / Math.max (parameters.width, parameters.height)) / Math.log (2);
}

bigshot.ImageTileCache.prototype = {
    resetUsed : function () {
        this.usedImages = {};
    },
    
    setMaxTiles : function (mtx, mty) {
        this.maxTileX = mtx;
        this.maxTileY = mty;
    },
    
    getPartialImage : function (tileX, tileY, zoomLevel) {
        var img = this.getPartialImageFromDownsampled (tileX, tileY, zoomLevel, 0, 0, this.parameters.tileSize, this.parameters.tileSize);
        if (img == null) {
            img = this.getPartialImageFromPoster (tileX, tileY, zoomLevel);
        }
        return img;
    },
    
    getPartialImageFromPoster : function (tileX, tileY, zoomLevel) {
        if (this.fullImage && this.fullImage.complete) {
            var posterScale = this.fullImage.width / this.parameters.width;
            var tileSizeAtZoom = posterScale * this.parameters.tileSize / Math.pow (2, zoomLevel);
            
            x0 = Math.floor (tileSizeAtZoom * tileX);
            y0 = Math.floor (tileSizeAtZoom * tileY);
            w = Math.floor (tileSizeAtZoom);
            h = Math.floor (tileSizeAtZoom);
            
            return this.createPartialImage (this.fullImage, this.fullImage.width, x0, y0, w, h);
        } else {
            return null;
        }
    },
    
    createPartialImage : function (sourceImage, expectedSourceImageSize, x0, y0, w, h) {
        var canvas = document.createElement ("canvas");
        if (!canvas["width"]) {
            return null;
        }
        canvas.width = this.partialImageSize;
        canvas.height = this.partialImageSize;
        var ctx = canvas.getContext('2d'); 
        
        var scale = sourceImage.width / expectedSourceImageSize;
        
        var sx = Math.floor (x0 * scale);
        var sy = Math.floor (y0 * scale);
        var dw = this.partialImageSize;
        var dh = this.partialImageSize;
        
        w *= scale;
        if (sx + w >= sourceImage.width) {
            var w0 = w;
            w = sourceImage.width - sx;
            dw *= w / w0;
        }
        
        h *= scale;
        if (sy + h >= sourceImage.height) {
            var h0 = h;
            h = sourceImage.height - sy;
            dh *= h / h0;
        }
        
        try {
            ctx.drawImage (sourceImage, sx, sy, w, h, -0.1, -0.1, dw + 0.2, dh + 0.2);
        } catch (e) {
            // DOM INDEX error on iPad.
            return null;
        }
        
        return canvas;
    },
    
    getPartialImageFromDownsampled : function (tileX, tileY, zoomLevel, x0, y0, w, h) {
        // Give up if the poster image has higher resolution.
        if (zoomLevel < this.POSTER_ZOOM_LEVEL || zoomLevel < this.parameters.minZoom) {
            return null;
        }
        
        var key = this.getImageKey (tileX, tileY, zoomLevel);
        var sourceImage = this.cachedImages[key];
        
        if (sourceImage == null) {
            this.requestImage (tileX, tileY, zoomLevel);
        }
        
        if (sourceImage) {
            return this.createPartialImage (sourceImage, this.parameters.tileSize, x0, y0, w, h);
        } else {
            w /= 2;
            h /= 2;
            x0 /= 2;
            y0 /= 2;
            if ((tileX % 2) == 1) {
                x0 += this.parameters.tileSize / 2;
            }
            if ((tileY % 2) == 1) {
                y0 += this.parameters.tileSize / 2;
            }
            tileX = Math.floor (tileX / 2);
            tileY = Math.floor (tileY / 2);
            --zoomLevel;
            return this.getPartialImageFromDownsampled (tileX, tileY, zoomLevel, x0, y0, w, h);
        }        
    },
    
    getEmptyImage : function () {
        var tile = document.createElement ("img");
        if (this.parameters.emptyImage) {
            tile.src = this.parameters.emptyImage;
        } else {
            tile.src = "data:image/gif,GIF89a%01%00%01%00%80%00%00%00%00%00%FF%FF%FF!%F9%04%00%00%00%00%00%2C%00%00%00%00%01%00%01%00%00%02%02D%01%00%3B";
        }
        return tile;
    },
    
    getImage : function (tileX, tileY, zoomLevel) {
        if (tileX < 0 || tileY < 0 || tileX >= this.maxTileX || tileY >= this.maxTileY) {
            return this.getEmptyImage ();
        }
        
        var key = this.getImageKey (tileX, tileY, zoomLevel);
        this.lruMap.access (key);
        
        if (this.cachedImages[key]) {
            if (this.usedImages[key]) {
                var tile = this.parameters.dataLoader.loadImage (this.getImageFilename (tileX, tileY, zoomLevel));
                tile.isPartial = false;
                return tile;
            } else {
                this.usedImages[key] = true;
                var img = this.cachedImages[key];
                return img;
            }
        } else {
            this.requestImage (tileX, tileY, zoomLevel);
            var img = this.getPartialImage (tileX, tileY, zoomLevel);
            if (img != null) {
                img.isPartial = true;
                this.cachedImages[key] = img;
            } else {
                img = this.getEmptyImage ();
                if (img != null) {
                    img.isPartial = true;
                }
            }
            return img;
        }
    },
    
    requestImage : function (tileX, tileY, zoomLevel) {
        var key = this.getImageKey (tileX, tileY, zoomLevel);
        if (!this.requestedImages[key]) {
            this.imageRequests++;
            var that = this;
            this.requestedImages[key] = true;
            this.parameters.dataLoader.loadImage (this.getImageFilename (tileX, tileY, zoomLevel), function (tile) {
                    delete that.requestedImages[key];
                    that.imageRequests--;
                    tile.isPartial = false;
                    that.cachedImages[key] = tile;
                    that.fireOnLoad ();
                });            
        }            
    },
    
    /**
     * Fires the onload event, if it hasn't been fired for at least 50 ms
     */
    fireOnLoad : function () {
        var now = new Date();
        if (this.imageRequests == 0 || now.getTime () > (this.lastOnLoadFiredAt + 50)) {
            this.purgeCache ();
            this.lastOnLoadFiredAt = now.getTime ();
            this.onLoaded ();
        }
    },
    
    /**
     * Removes the least-recently used objects from the cache,
     * if the size of the cache exceeds the maximum cache size.
     * A maximum of four objects will be removed per call.
     *
     * @private
     */
    purgeCache : function () {
        for (var i = 0; i < 4; ++i) {
            if (this.lruMap.getSize () > this.maxCacheSize) {
                var leastUsed = this.lruMap.leastUsed ();
                this.lruMap.remove (leastUsed);
                delete this.cachedImages[leastUsed];                    
            }
        }
    },
    
    getImageKey : function (tileX, tileY, zoomLevel) {
        return "I" + tileX + "_" + tileY + "_" + zoomLevel;
    },
    
    getImageFilename : function (tileX, tileY, zoomLevel) {
        var f = this.parameters.fileSystem.getImageFilename (tileX, tileY, zoomLevel);
        return f;
    }
};
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new image parameter object and populates it with default values for
 * all values not explicitly given.
 *
 * @class ImageParameters parameter object.
 * You need not set any fields that can be read from the image descriptor that 
 * MakeImagePyramid creates. See the {@link bigshot.Image} documentation for 
 * required parameters.
 *
 * <p>Usage:
 *
 * @example
 * var bsi = new bigshot.Image (
 *     new bigshot.ImageParameters ({
 *         basePath : "/bigshot.php?file=myshot.bigshot",
 *         fileSystemType : "archive",
 *         container : document.getElementById ("bigshot_div")
 *         }));
 * 
 * @param values named parameter map, see the fields below for parameter names and types.
 * @see bigshot.Image
 */
bigshot.ImageParameters = function (values) {
    /**
     * Size of low resolution preview image along the longest image
     * dimension. The preview is assumed to have the same aspect
     * ratio as the full image (specified by width and height).
     *
     * @default <i>Optional</i> set by MakeImagePyramid and loaded from descriptor
     * @type int
     * @public
     */
    this.posterSize = 0;
    
    /**
     * Url for the image tile to show while the tile is loading and no 
     * low-resolution preview is available.
     *
     * @default <code>null</code>, which results in an all-black image
     * @type String
     * @public
     */
    this.emptyImage = null;
    
    /**
     * Suffix to append to the tile filenames. Typically <code>".jpg"</code> or 
     * <code>".png"</code>.
     *
     * @default <i>Optional</i> set by MakeImagePyramid and loaded from descriptor
     * @type String
     */
    this.suffix = null;
    
    /**
     * The width of the full image; in pixels.
     *
     * @default <i>Optional</i> set by MakeImagePyramid and loaded from descriptor
     * @type int
     */
    this.width = 0;
    
    /**
     * The height of the full image; in pixels.
     *
     * @default <i>Optional</i> set by MakeImagePyramid and loaded from descriptor
     * @type int
     */
    this.height = 0;
    
    /**
     * For {@link bigshot.Image} and {@link bigshot.SimpleImage}, the <code>div</code> 
     * to use as a container for the image.
     *
     * @type HTMLDivElement
     */
    this.container = null;
    
    /**
     * The minimum zoom value. Zoom values are specified as a magnification; where
     * 2<sup>n</sup> is the magnification and n is the zoom value. So a zoom value of
     * 2 means a 4x magnification of the full image. -3 means showing an image that
     * is a eighth (1/8 or 1/2<sup>3</sup>) of the full size.
     *
     * @type number
     * @default <i>Optional</i> set by MakeImagePyramid and loaded from descriptor
     */
    this.minZoom = 0.0;
    
    /**
     * The maximum zoom value. Zoom values are specified as a magnification; where
     * 2<sup>n</sup> is the magnification and n is the zoom value. So a zoom value of
     * 2 means a 4x magnification of the full image. -3 means showing an image that
     * is a eighth (1/8 or 1/2<sup>3</sup>) of the full size.
     *
     * @type number
     * @default 0.0
     */
    this.maxZoom = 0.0;
    
    /**
     * Size of one tile in pixels.
     *
     * @type int
     * @default <i>Optional</i> set by MakeImagePyramid and loaded from descriptor
     */
    this.tileSize = 0;
    
    /**
     * Tile overlap. Not implemented.
     *
     * @type int
     * @default <i>Optional</i> set by MakeImagePyramid and loaded from descriptor
     */
    this.overlap = 0;
    
    /**
     * Flag indicating that the image should wrap horizontally. The image wraps on tile
     * boundaries; so in order to get a seamless wrap at zoom level -n; the image width must
     * be evenly divisible by <code>tileSize * 2^n</code>. Set the minZoom value appropriately.
     * 
     * @type boolean
     * @default false
     */
    this.wrapX = false;
    
    /**
     * Flag indicating that the image should wrap vertically. The image wraps on tile
     * boundaries; so in order to get a seamless wrap at zoom level -n; the image height must
     * be evenly divisible by <code>tileSize * 2^n</code>. Set the minZoom value appropriately.
     *
     * @type boolean
     * @default false
     */
    this.wrapY = false;
    
    /**
     * Base path for the image. This is filesystem dependent; but for the two most common cases
     * the following should be set
     *
     * <ul>
     * <li><b>archive</b>= The basePath is <code>"&lt;path&gt;/bigshot.php?file=&lt;path-to-bigshot-archive-relative-to-bigshot.php&gt;"</code>;
     *     for example; <code>"/bigshot.php?file=images/bigshot-sample.bigshot"</code>.
     * <li><b>folder</b>= The basePath is <code>"&lt;path-to-image-folder&gt;"</code>;
     *     for example; <code>"/images/bigshot-sample"</code>.
     * </ul>
     *
     * @type String
     */
    this.basePath = null;
    
    /**
     * The file system type. Used to create a filesystem instance unless
     * the fileSystem field is set. Possible values are <code>"archive"</code>, 
     * <code>"folder"</code> or <code>"dzi"</code>.
     *
     * @type String
     * @default "folder"
     */
    this.fileSystemType = "folder";
    
    /**
     * A reference to a filesystem implementation. If set; it overrides the
     * fileSystemType field.
     *
     * @default set depending on value of bigshot.ImageParameters.fileSystemType
     * @type bigshot.FileSystem
     */
    this.fileSystem = null;
    
    /**
     * Object used to load data files.
     *
     * @default bigshot.DefaultDataLoader
     * @type bigshot.DataLoader
     */
    this.dataLoader = new bigshot.DefaultDataLoader ();
    
    /**
     * Enable the touch-friendly ui. The touch-friendly UI splits the viewport into
     * three click-sensitive regions:
     * <p style="text-align:center"><img src="../images/touch-ui.png"/></p>
     * 
     * <p>Clicking (or tapping with a finger) on the outer region causes the viewport to zoom out.
     * Clicking anywhere within the middle, "pan", region centers the image on the spot clicked.
     * Finally, clicking in the center hotspot will center the image on the spot clicked and zoom
     * in half a zoom level.
     *
     * <p>As before, you can drag to pan anywhere.
     *
     * <p>If you have navigation tools for mouse users that hover over the image container, it 
     * is recommended that any click events on them are kept from bubbling, otherwise the click 
     * will propagate to the touch ui. One way is to use the 
     * {@link bigshot.Browser#stopMouseEventBubbling} method:
     *
     * @example
     * var browser = new bigshot.Browser ();
     * browser.stopMouseEventBubbling (document.getElementById ("myBigshotControlDiv"));
     *
     * @see bigshot.ImageBase#showTouchUI
     *
     * @type boolean
     * @default true
     * @deprecated Bigshot supports all common touch-gestures.
     */
    this.touchUI = false;
    
    /**
     * Lets you "fling" the image.
     * 
     * @type boolean
     * @default true
     */
    this.fling = true;
    
    /**
     * The maximum amount that a tile will be stretched until we try to show
     * the next more detailed level.
     *
     * @type float
     * @default 1.0
     */
    this.maxTextureMagnification = 1.0;
    
    if (values) {
        for (var k in values) {
            this[k] = values[k];
        }
    }
    
    this.merge = function (values, overwrite) {
        for (var k in values) {
            if (overwrite || !this[k]) {
                this[k] = values[k];
            }
        }
    }
    return this;        
};
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */


/**
 * Sets up base image functionality.
 * 
 * @param {bigshot.ImageParameters} parameters the image parameters
 * @class Base class for image viewers.
 * @extends bigshot.EventDispatcher
 */     
bigshot.ImageBase = function (parameters) {
    // Base class init
    bigshot.EventDispatcher.call (this);
    
    this.parameters = parameters;
    this.flying = 0;
    this.container = parameters.container;
    this.x = parameters.width / 2.0;
    this.y = parameters.height / 2.0;
    this.zoom = 0.0;
    this.width = parameters.width;
    this.height = parameters.height;
    this.minZoom = parameters.minZoom;
    this.maxZoom = parameters.maxZoom;
    this.tileSize = parameters.tileSize;
    this.overlap = 0;
    this.imageTileCache = null;
    
    this.dragStart = null;
    this.dragged = false;
    
    this.layers = new Array ();
    
    this.fullScreenHandler = null;
    this.currentGesture = null;
    
    var that = this;
    this.onresizeHandler = function (e) {
        that.onresize ();
    }
    
    /**
     * Helper function to consume events.
     * @private
     */
    var consumeEvent = function (event) {
        if (event.preventDefault) {
            event.preventDefault ();
        }
        return false;
    };
    
    /**
     * Helper function to translate touch events to mouse-like events.
     * @private
     */
    var translateEvent = function (event) {
        if (event.clientX) {
            return event;
        } else {
            return {
                clientX : event.changedTouches[0].clientX,
                clientY : event.changedTouches[0].clientY,
                changedTouches : event.changedTouches
            };
        };
    };
    
    this.setupLayers ();
    
    this.resize ();
    
    this.allListeners = {
        "DOMMouseScroll" : function (e) {
            that.mouseWheel (e);
            return consumeEvent (e);
        },
        "mousewheel" : function (e) {
            that.mouseWheel (e);
            return consumeEvent (e);
        },
        "dblclick" : function (e) {
            that.mouseDoubleClick (e);
            return consumeEvent (e);
        },
        "mousedown" : function (e) {
            that.dragMouseDown (e);
            return consumeEvent (e);
        },
        "gesturestart" : function (e) {
            that.gestureStart (e);
            return consumeEvent (e);
        },
        "gesturechange" : function (e) {
            that.gestureChange (e);
            return consumeEvent (e);
        },
        "gestureend" : function (e) {
            that.gestureEnd (e);
            return consumeEvent (e);
        },
        "touchstart" : function (e) {
            that.dragMouseDown (translateEvent (e));
            return consumeEvent (e);
        },
        "mouseup" : function (e) {
            that.dragMouseUp (e);
            return consumeEvent (e);
        },
        "touchend" : function (e) {
            that.dragMouseUp (translateEvent (e));
            return consumeEvent (e);
        },
        "mousemove" : function (e) {
            that.dragMouseMove (e);
            return consumeEvent (e);
        },
        "mouseout" : function (e) {
            //that.dragMouseUp (e);
            return consumeEvent (e);
        },
        "touchmove" : function (e) {
            that.dragMouseMove (translateEvent (e));
            return consumeEvent (e);
        }
    };
    
    this.addEventListeners ();
    this.browser.registerListener (window, 'resize', that.onresizeHandler, false);
    this.zoomToFit ();
}    

bigshot.ImageBase.prototype = {
    /**
     * Browser helper and compatibility functions.
     *
     * @private
     * @type bigshot.Browser
     */
    browser : new bigshot.Browser (),
    
    /**
     * Adds all event listeners to the container object.
     * @private
     */
    addEventListeners : function () {
        for (var k in this.allListeners) {
            this.browser.registerListener (this.container, k, this.allListeners[k], false);
        }
    },
    
    /**
     * Removes all event listeners from the container object.
     * @private
     */
    removeEventListeners : function () {
        for (var k in this.allListeners) {
            this.browser.unregisterListener (this.container, k, this.allListeners[k], false);
        }
    },
    
    /**
     * Sets up the initial layers of the image. Override in subclass.
     */
    setupLayers : function () {
    },
    
    /**
     * Returns the base 2 logarithm of the maximum texture stretching, allowing for device pixel scaling.
     * @type number
     * @private
     */
    getTextureStretch : function () {
        var ts = Math.log (this.parameters.maxTextureMagnification / this.browser.getDevicePixelScale ()) / Math.LN2;
        return ts;
    },
    
    /**
     * Constrains the x and y coordinates to allowed values
     * @param {number} x the initial x coordinate
     * @param {number} y the initial y coordinate
     * @return {number} .x the constrained x coordinate
     * @return {number} .y the constrained y coordinate
     */
    clampXY : function (x, y) {
        var viewportWidth = this.container.clientWidth;
        var viewportHeight = this.container.clientHeight;
        
        var realZoomFactor = Math.pow (2, this.zoom);
        /*
        Constrain X and Y
        */
        var viewportWidthInImagePixels = viewportWidth / realZoomFactor;
        var viewportHeightInImagePixels = viewportHeight / realZoomFactor;
        
        var constrain = function (viewportSizeInImagePixels, imageSizeInImagePixels, p) {
            var min = viewportSizeInImagePixels / 2;
            min = Math.min (imageSizeInImagePixels / 2, min);
            if (p < min) {
                p = min;
            }
            
            var max = imageSizeInImagePixels - viewportSizeInImagePixels / 2;
            max = Math.max (imageSizeInImagePixels / 2, max);
            if (p > max) {
                p = max;
            }
            return p;
        };
        
        var o = {};
        if (x != null) {
            o.x = constrain (viewportWidthInImagePixels, this.width, x);
        }
        
        if (y != null) {
            o.y = constrain (viewportHeightInImagePixels, this.height, y);
        }
        
        return o;
    },
    
    /**
     * Lays out all layers according to the current 
     * x, y and zoom values.
     *
     * @public
     */
    layout : function () {
        var viewportWidth = this.container.clientWidth;
        var viewportHeight = this.container.clientHeight;
        
        var zoomWithStretch = Math.min (this.maxZoom, Math.max (this.zoom - this.getTextureStretch (), this.minZoom));
        
        var zoomLevel = Math.min (0, Math.ceil (zoomWithStretch));
        var zoomFactor = Math.pow (2, zoomLevel);
        
        var clamped = this.clampXY (this.x, this.y);
        
        if (!this.parameters.wrapY) {
            this.y = clamped.y;
        }
        
        if (!this.parameters.wrapX) {
            this.x = clamped.x;
        }
        
        var tileWidthInRealPixels = this.tileSize / zoomFactor;
        
        var fractionalZoomFactor = Math.pow (2, this.zoom - zoomLevel);
        var tileDisplayWidth = this.tileSize * fractionalZoomFactor;
        
        var widthInTiles = this.width / tileWidthInRealPixels;
        var heightInTiles = this.height / tileWidthInRealPixels;
        var centerInTilesX = this.x / tileWidthInRealPixels;
        var centerInTilesY = this.y / tileWidthInRealPixels;
        
        var topLeftInTilesX = centerInTilesX - (viewportWidth / 2) / tileDisplayWidth;
        var topLeftInTilesY = centerInTilesY - (viewportHeight / 2) / tileDisplayWidth;
        
        var topLeftTileX = Math.floor (topLeftInTilesX);
        var topLeftTileY = Math.floor (topLeftInTilesY);
        var topLeftTileXoffset = Math.round ((topLeftInTilesX - topLeftTileX) * tileDisplayWidth);
        var topLeftTileYoffset = Math.round ((topLeftInTilesY - topLeftTileY) * tileDisplayWidth);
        
        for (var i = 0; i < this.layers.length; ++i) {
            this.layers[i].layout (
                zoomWithStretch, 
                -topLeftTileXoffset - tileDisplayWidth, -topLeftTileYoffset - tileDisplayWidth, 
                topLeftTileX - 1, topLeftTileY - 1, 
                Math.ceil (tileDisplayWidth), Math.ceil (tileDisplayWidth), 
                1.0);
        }
    },
    
    /**
     * Resizes the layers of this image.
     *
     * @public
     */
    resize : function () {
        var tilesW = Math.ceil (2 * this.container.clientWidth / this.tileSize) + 2;
        var tilesH = Math.ceil (2 * this.container.clientHeight / this.tileSize) + 2;
        for (var i = 0; i < this.layers.length; ++i) {
            this.layers[i].resize (tilesW, tilesH);
        }
    },
    
    /**
     * Creates a HTML div container for a layer. This method
     * is called by the layer's constructor to obtain a 
     * container.
     *
     * @public
     * @type HTMLDivElement
     */
    createLayerContainer : function () {
        var layerContainer = document.createElement ("div");
        layerContainer.style.position = "absolute";
        layerContainer.style.overflow = "hidden";
        return layerContainer;
    },
    
    /**
     * Returns the div element used as viewport.
     *
     * @public
     * @type HTMLDivElement
     */
    getContainer : function () {
        return this.container;
    },
    
    /**
     * Adds a new layer to the image.
     *
     * @public
     * @see bigshot.HotspotLayer for usage example
     * @param {bigshot.Layer} layer the layer to add.
     */
    addLayer : function (layer) {
        this.container.appendChild (layer.getContainer ());
        this.layers.push (layer);
    },
    
    /**
     * Clamps the zoom value to be between minZoom and maxZoom.
     *
     * @param {number} zoom the zoom value
     * @type number
     */
    clampZoom : function (zoom) {
        return Math.min (this.maxZoom, Math.max (zoom, this.minZoom));
    },
    
    /**
     * Sets the current zoom value.
     *
     * @private
     * @param {number} zoom the zoom value.
     * @param {boolean} [layout] trigger a viewport update after setting. Defaults to <code>false</code>.
     */
    setZoom : function (zoom, updateViewport) {
        this.zoom = this.clampZoom (zoom);
        var zoomLevel = Math.ceil (this.zoom - this.getTextureStretch ());
        var zoomFactor = Math.pow (2, zoomLevel);
        var maxTileX = Math.ceil (zoomFactor * this.width / this.tileSize);
        var maxTileY = Math.ceil (zoomFactor * this.height / this.tileSize);
        for (var i = 0; i < this.layers.length; ++i) {
            this.layers[i].setMaxTiles (maxTileX, maxTileY);
        }
        if (updateViewport) {
            this.layout ();
        }
    },
    
    /**
     * Sets the maximum zoom value. The maximum magnification (of the full-size image)
     * is 2<sup>maxZoom</sup>. Set to 0.0 to avoid pixelation.
     *
     * @public
     * @param {number} maxZoom the maximum zoom value
     */
    setMaxZoom : function (maxZoom) {
        this.maxZoom = maxZoom;
    },
    
    /**
     * Gets the maximum zoom value. The maximum magnification (of the full-size image)
     * is 2<sup>maxZoom</sup>.
     * 
     * @public
     * @type number
     */
    getMaxZoom : function () {
        return this.maxZoom;
    },
    
    /**
     * Sets the minimum zoom value. The minimum magnification (of the full-size image)
     * is 2<sup>minZoom</sup>, so a minZoom of <code>-3</code> means that the smallest
     * image shown will be one-eighth of the full-size image.
     *
     * @public
     * @param {number} minZoom the minimum zoom value for this image
     */
    setMinZoom : function (minZoom) {
        this.minZoom = minZoom;
    },
    
    /**
     * Gets the minimum zoom value. The minimum magnification (of the full-size image)
     * is 2<sup>minZoom</sup>, so a minZoom of <code>-3</code> means that the smallest
     * image shown will be one-eighth of the full-size image.
     * 
     * @public
     * @type number
     */
    getMinZoom : function () {
        return this.minZoom;
    },
    
    /**
     * Adjusts a coordinate so that the center of zoom
     * remains constant during zooming operations. The
     * method is intended to be called twice, once for x 
     * and once for y. The <code>current</code> and 
     * <code>centerOfZoom</code> values will be the current
     * and the center for the x and y, respectively.
     *
     * @example
     * this.x = this.adjustCoordinateForZoom (this.x, zoomCenterX, oldZoom, newZoom);
     * this.y = this.adjustCoordinateForZoom (this.y, zoomCenterY, oldZoom, newZoom);
     *
     * @param {number} current the current value of the coordinate
     * @param {number} centerOfZoom the center of zoom along the coordinate axis
     * @param {number} oldZoom the old zoom value
     * @param {number} oldZoom the new zoom value 
     * @type number
     * @returns the new value for the coordinate
     */
    adjustCoordinateForZoom : function (current, centerOfZoom, oldZoom, newZoom) {
        var zoomRatio = Math.pow (2, oldZoom) / Math.pow (2, newZoom);
        return centerOfZoom + (current - centerOfZoom) * zoomRatio;
    },
    
    /**
     * Begins a potential drag event.
     *
     * @private
     */
    gestureStart : function (event) {
        this.currentGesture = {
            startZoom : this.zoom,
            scale : event.scale
        };            
    },
    
    /**
     * Ends a gesture.
     *
     * @param {Event} event the <code>gestureend</code> event
     * @private
     */
    gestureEnd : function (event) {
        this.currentGesture = null;
        if (this.dragStart) {
            this.dragStart.hadGesture = true;
        }
    },
    
    /**
     * Adjusts the zoom level based on the scale property of the
     * gesture.
     *
     * @private
     */
    gestureChange : function (event) {
        if (this.currentGesture) {
            if (this.dragStart) {
                this.dragStart.hadGesture = true;
            }
            
            var newZoom = this.clampZoom (this.currentGesture.startZoom + Math.log (event.scale) / Math.log (2));
            var oldZoom = this.getZoom ();
            if (this.currentGesture.clientX !== undefined && this.currentGesture.clientY !== undefined) {
                var centerOfZoom = this.clientToImage (this.currentGesture.clientX, this.currentGesture.clientY);
                
                var nx = this.adjustCoordinateForZoom (this.x, centerOfZoom.x, oldZoom, newZoom);
                var ny = this.adjustCoordinateForZoom (this.y, centerOfZoom.y, oldZoom, newZoom);
                
                this.moveTo (nx, ny, newZoom);
            } else {
                this.setZoom (newZoom);
                this.layout ();
            }
        }
    },
    
    /**
     * Begins a potential drag event.
     *
     * @private
     */
    dragMouseDown : function (event) {
        this.dragStart = {
            x : event.clientX,
            y : event.clientY
        };
        this.dragLast = {
            clientX : event.clientX,
            clientY : event.clientY,
            dx : 0,
            dy : 0,
            dt : 1000000,
            time : new Date ().getTime ()
        };
        this.dragged = false;
    },
    
    /**
     * Handles a mouse drag event by panning the image.
     * Also sets the dragged flag to indicate that the
     * following <code>click</code> event should be ignored.
     * @private
     */
    dragMouseMove : function (event) {
        if (this.currentGesture != null && event.changedTouches != null && event.changedTouches.length > 0) {
            var cx = 0;
            var cy = 0;
            for (var i = 0; i < event.changedTouches.length; ++i) {
                cx += event.changedTouches[i].clientX;
                cy += event.changedTouches[i].clientY;
            }
            this.currentGesture.clientX = cx / event.changedTouches.length;
            this.currentGesture.clientY = cy / event.changedTouches.length;
        }        
        
        if (this.currentGesture == null && this.dragStart != null) {
            var delta = {
                x : event.clientX - this.dragStart.x,
                y : event.clientY - this.dragStart.y
            };
            if (delta.x != 0 || delta.y != 0) {
                this.dragged = true;
            }
            var zoomFactor = Math.pow (2, this.zoom);
            var realX = delta.x / zoomFactor;
            var realY = delta.y / zoomFactor;
            
            this.dragStart = {
                x : event.clientX,
                y : event.clientY
            };
            
            var dt = new Date ().getTime () - this.dragLast.time;
            if (dt > 20) {
                this.dragLast = {
                    dx : this.dragLast.clientX - event.clientX,
                    dy : this.dragLast.clientY - event.clientY,
                    dt : dt,
                    clientX : event.clientX,
                    clientY : event.clientY,
                    time : new Date ().getTime ()
                };
            }
            
            this.moveTo (this.x - realX, this.y - realY);
        }
    },
    
    /**
     * Ends a drag event by freeing the associated structures.
     * @private
     */
    dragMouseUp : function (event) {
        if (this.currentGesture == null && !this.dragStart.hadGesture && this.dragStart != null) {
            this.dragStart = null;
            if (!this.dragged) {
                this.mouseClick (event);
            } else {
                var scale = Math.pow (2, this.zoom);
                var dx = this.dragLast.dx / scale;
                var dy = this.dragLast.dy / scale;
                var ds = Math.sqrt (dx * dx + dy * dy);
                var dt = this.dragLast.dt;
                var dtb = new Date ().getTime () - this.dragLast.time;
                this.dragLast = null;
                
                var v = dt > 0 ? (ds / dt) : 0;
                if (v > 0.05 && dtb < 250 && dt > 20 && this.parameters.fling) {
                    var t0 = new Date ().getTime ();
                    
                    dx /= dt;
                    dy /= dt;
                    
                    this.flyTo (this.x + dx * 250, this.y + dy * 250, this.zoom);
                }   
            }
        }
    },
    
    /**
     * Mouse double-click handler. Pans to the clicked point and
     * zooms in half a zoom level (approx 40%).
     * @private
     */
    mouseDoubleClick : function (event) {
        var eventData = this.createImageEventData ({
                type : "dblclick",
                clientX : event.clientX,
                clientY : event.clientY
            });
        this.fireEvent ("dblclick", eventData);
        if (!eventData.defaultPrevented) {
            this.flyTo (eventData.imageX, eventData.imageY, this.zoom + 0.5);
        }
    },
    
    /**
     * Returns the current zoom level.
     *
     * @public
     * @type number
     */
    getZoom : function () {
        return this.zoom;
    },
    
    /**
     * Stops any current flyTo operation and sets the current position.
     *
     * @param [x] the new x-coordinate
     * @param [y] the new y-coordinate
     * @param [zoom] the new zoom level
     * @param [updateViewport=true] updates the viewport
     * @public
     */
    moveTo : function (x, y, zoom, updateViewport) {
        this.stopFlying ();
        
        if (x != null || y != null) {
            this.setPosition (x, y, false);
        }
        if (zoom != null) {
            this.setZoom (zoom, false);
        }
        if (updateViewport == undefined || updateViewport == true) {
            this.layout ();
        }
    },
    
    /**
     * Sets the current position.
     *
     * @param [x] the new x-coordinate
     * @param [y] the new y-coordinate
     * @param [updateViewport=true] if the viewport should be updated
     * @private
     */
    setPosition : function (x, y, updateViewport) {
        var clamped = this.clampXY (x, y);
        
        if (x != null) {
            if (this.parameters.wrapX) {
                if (x < 0 || x >= this.width) {
                    x = (x + this.width) % this.width;
                }
            } else {
                x = clamped.x;
            }
            this.x = Math.max (0, Math.min (this.width, x));
        }
        
        if (y != null) {
            if (this.parameters.wrapY) {
                if (y < 0 || y >= this.height) {
                    y = (y + this.height) % this.height;
                }
            } else {
                y = clamped.y;
            }
            this.y = Math.max (0, Math.min (this.height, y));
        }
        
        if (updateViewport != false) {
            this.layout ();
        }
    },
    
    /**
     * Helper function for calculating zoom levels.
     *
     * @public
     * @returns the zoom level at which the given number of full-image pixels
     * occupy the given number of screen pixels.
     * @param {number} imageDimension the image dimension in full-image pixels
     * @param {number} containerDimension the container dimension in screen pixels
     * @type number
     */
    fitZoom : function (imageDimension, containerDimension) {
        var scale = containerDimension / imageDimension;
        return Math.log (scale) / Math.LN2;
    },
    
    /**
     * Returns the maximum zoom level at which the full image
     * is visible in the viewport.
     * @public
     * @type number
     */
    getZoomToFitValue : function () {
        return Math.min (
            this.fitZoom (this.parameters.width, this.container.clientWidth),
            this.fitZoom (this.parameters.height, this.container.clientHeight));
    },
    
    /**
     * Returns the zoom level at which the image fills the whole
     * viewport.
     * @public
     * @type number
     */
    getZoomToFillValue : function () {
        return Math.max (
            this.fitZoom (this.parameters.width, this.container.clientWidth),
            this.fitZoom (this.parameters.height, this.container.clientHeight));
    },
    
    /**
     * Adjust the zoom level to fit the image in the viewport.
     * @public
     */
    zoomToFit : function () {
        this.moveTo (null, null, this.getZoomToFitValue ());
    },
    
    /**
     * Adjust the zoom level to fit the image in the viewport.
     * @public
     */
    zoomToFill : function () {
        this.moveTo (null, null, this.getZoomToFillValue ());
    },
    
    /**
     * Adjust the zoom level to fit the 
     * image height in the viewport.
     * @public
     */
    zoomToFitHeight : function () {
        this.moveTo (null, null, this.fitZoom (this.parameters.height, this.container.clientHeight));
    },
    
    /**
     * Adjust the zoom level to fit the 
     * image width in the viewport.
     * @public
     */
    zoomToFitWidth : function () {
        this.moveTo (null, null, this.fitZoom (this.parameters.width, this.container.clientWidth));
    },
    
    /**
     * Smoothly adjust the zoom level to fit the 
     * image height in the viewport.
     * @public
     */
    flyZoomToFitHeight : function () {
        this.flyTo (null, this.parameters.height / 2, this.fitZoom (this.parameters.height, this.container.clientHeight));
    },
    
    /**
     * Smoothly adjust the zoom level to fit the 
     * image width in the viewport.
     * @public
     */
    flyZoomToFitWidth : function () {
        this.flyTo (this.parameters.width / 2, null, this.fitZoom (this.parameters.width, this.container.clientWidth));
    },
    
    /**
     * Smoothly adjust the zoom level to fit the 
     * full image in the viewport.
     * @public
     */
    flyZoomToFit : function () {
        this.flyTo (this.parameters.width / 2, this.parameters.height / 2, this.getZoomToFitValue ());
    },
    
    /**
     * Converts client-relative screen coordinates to image coordinates.
     *
     * @param {number} clientX the client x-coordinate
     * @param {number} clientY the client y-coordinate
     *
     * @returns {number} .x the image x-coordinate
     * @returns {number} .y the image y-coordinate
     * @type Object
     */
    clientToImage : function (clientX, clientY) {
        var zoomFactor = Math.pow (2, this.zoom);
        return {
            x : (clientX - this.container.clientWidth / 2) / zoomFactor + this.x,
            y : (clientY - this.container.clientHeight / 2) / zoomFactor + this.y
        };
    },
    
    /**
     * Handles mouse wheel actions.
     * @private
     */
    mouseWheelHandler : function (delta, event) {
        var zoomDelta = false;
        if (delta > 0) {
            zoomDelta = 0.5;
        } else if (delta < 0) {
            zoomDelta = -0.5;
        }
        
        if (zoomDelta) {
            var centerOfZoom = this.clientToImage (event.clientX, event.clientY);
            var newZoom = Math.min (this.maxZoom, Math.max (this.getZoom () + zoomDelta, this.minZoom));
            
            var nx = this.adjustCoordinateForZoom (this.x, centerOfZoom.x, this.getZoom (), newZoom);
            var ny = this.adjustCoordinateForZoom (this.y, centerOfZoom.y, this.getZoom (), newZoom);
            
            this.flyTo (nx, ny, newZoom, true);
        }
    },
    
    /**
     * Translates mouse wheel events.
     * @private
     */
    mouseWheel : function (event){
        var delta = 0;
        if (!event) /* For IE. */
            event = window.event;
        if (event.wheelDelta) { /* IE/Opera. */
            delta = event.wheelDelta / 120;
            /*
             * In Opera 9, delta differs in sign as compared to IE.
             */
            if (window.opera)
                delta = -delta;
        } else if (event.detail) { /* Mozilla case. */
            /*
             * In Mozilla, sign of delta is different than in IE.
             * Also, delta is multiple of 3.
             */
            delta = -event.detail;
        }
        
        /*
         * If delta is nonzero, handle it.
         * Basically, delta is now positive if wheel was scrolled up,
         * and negative, if wheel was scrolled down.
         */
        if (delta) {
            this.mouseWheelHandler (delta, event);
        }
        
        /*
         * Prevent default actions caused by mouse wheel.
         * That might be ugly, but we handle scrolls somehow
         * anyway, so don't bother here..
         */
        if (event.preventDefault) {
            event.preventDefault ();
        }
        event.returnValue = false;
    },
    
    /**
     * Triggers a right-sizing of all layers.
     * Called on window resize via the {@link bigshot.ImageBase#onresizeHandler} stub.
     * @public
     */
    onresize : function () {
        this.resize ();
        this.layout ();
    },
    
    /**
     * Returns the current x-coordinate, which is the full-image x coordinate
     * in the center of the viewport.
     * @public
     * @type number
     */
    getX : function () {
        return this.x;
    },
    
    /**
     * Returns the current y-coordinate, which is the full-image x coordinate
     * in the center of the viewport.
     * @public
     * @type number
     */
    getY : function () {
        return this.y;
    },
    
    /**
     * Interrupts the current {@link #flyTo}, if one is active.
     * @public
     */
    stopFlying : function () {
        this.flying++;
    },
    
    /**
     * Smoothly flies to the specified position.
     *
     * @public
     * @param {number} [x=current x] the new x-coordinate
     * @param {number} [y=current y] the new y-coordinate
     * @param {number} [zoom=current zoom] the new zoom level
     * @param {boolean} [uniformApproach=false] if true, uses the same interpolation curve for x, y and zoom.
     */
    flyTo : function (x, y, zoom, uniformApproach) {
        var that = this;
        
        x = x != null ? x : this.x;
        y = y != null ? y : this.y;
        zoom = zoom != null ? zoom : this.zoom;
        uniformApproach = uniformApproach != null ? uniformApproach : false;
        
        var startX = this.x;
        var startY = this.y;
        var startZoom = this.zoom;
        
        var clamped = this.clampXY (x, y);
        var targetX = this.parameters.wrapX ? x : clamped.x;
        var targetY = this.parameters.wrapY ? y : clamped.y;
        var targetZoom = Math.min (this.maxZoom, Math.max (zoom, this.minZoom));
        
        this.flying++;
        var flyingAtStart = this.flying;
        
        var t0 = new Date ().getTime ();
        
        var approach = function (start, target, dt, step, linear) {
            var delta = (target - start);
            
            var diff = - delta * Math.pow (2, -dt * step);
            
            var lin = dt * linear;
            if (delta < 0) {
                diff = Math.max (0, diff - lin);
            } else {
                diff = Math.min (0, diff + lin);
            }
            
            return target + diff;
        };
        
        
        var iter = function () {
            if (that.flying == flyingAtStart) {
                var dt = (new Date ().getTime () - t0) / 1000;
                
                var nx = approach (startX, targetX, dt, uniformApproach ? 10 : 4, uniformApproach ? 0.2 : 1.0);
                var ny = approach (startY, targetY, dt, uniformApproach ? 10 : 4, uniformApproach ? 0.2 : 1.0);
                var nz = approach (startZoom, targetZoom, dt, 10, 0.2);
                var done = true;
                
                var zoomFactor = Math.min (Math.pow (2, that.getZoom ()), 1);
                
                if (Math.abs (nx - targetX) < (0.5 * zoomFactor)) {
                    nx = targetX;
                } else {
                    done = false;
                }
                if (Math.abs (ny - targetY) < (0.5 * zoomFactor)) {
                    ny = targetY;
                } else {
                    done = false;
                }
                if (Math.abs (nz - targetZoom) < 0.02) {
                    nz = targetZoom;
                } else {
                    done = false;
                }
                that.setPosition (nx, ny, false);
                that.setZoom (nz, false);
                that.layout ();
                if (!done) {
                    that.browser.requestAnimationFrame (iter, that.container);
                }
            };
        }
        this.browser.requestAnimationFrame (iter, this.container);
    },
    
    /**
     * Returns the maximum zoom level at which a rectangle with the given dimensions
     * fit into the viewport.
     *
     * @public
     * @param {number} w the width of the rectangle, given in full-image pixels
     * @param {number} h the height of the rectangle, given in full-image pixels
     * @type number
     * @returns the zoom level that will precisely fit the given rectangle
     */        
    rectVisibleAtZoomLevel : function (w, h) {
        return Math.min (
            this.fitZoom (w, this.container.clientWidth),
            this.fitZoom (h, this.container.clientHeight));
    },
    
    /**
     * Returns the base size in screen pixels of the two zoom touch areas.
     * The zoom out border will be getTouchAreaBaseSize() pixels wide,
     * and the center zoom in hotspot will be 2*getTouchAreaBaseSize() pixels wide
     * and tall.
     * @deprecated
     * @type number
     * @public
     */
    getTouchAreaBaseSize : function () {
        var averageSize = ((this.container.clientWidth + this.container.clientHeight) / 2) * 0.2;
        return Math.min (averageSize, Math.min (this.container.clientWidth, this.container.clientHeight) / 6);
    },
    
    /**
     * Creates a new {@link bigshot.ImageEvent} using the supplied data object,
     * transforming the client x- and y-coordinates to local and image coordinates.
     * The returned event object will have the {@link bigshot.ImageEvent#localX}, 
     * {@link bigshot.ImageEvent#localY}, {@link bigshot.ImageEvent#imageX}, 
     * {@link bigshot.ImageEvent#imageY}, {@link bigshot.Event#target} and 
     * {@link bigshot.Event#currentTarget} fields set.
     *
     * @param {Object} data data object with initial values for the event object
     * @param {number} data.clientX the clientX of the event
     * @param {number} data.clientY the clientY of the event
     * @returns the new event object
     * @type bigshot.ImageEvent
     */
    createImageEventData : function (data) {
        var elementPos = this.browser.getElementPosition (this.container);
        data.localX = data.clientX - elementPos.x;
        data.localY = data.clientY - elementPos.y;
        
        var scale = Math.pow (2, this.zoom);
        
        data.imageX = (data.localX - this.container.clientWidth / 2) / scale + this.x;
        data.imageY = (data.localY - this.container.clientHeight / 2) / scale + this.y;
        
        data.target = this;
        data.currentTarget = this;
        
        return new bigshot.ImageEvent (data);
    },
    
    /**
     * Handles mouse click events. If the touch UI is active,
     * we'll pan and/or zoom, as appropriate. If not, we just ignore
     * the event.
     * @private
     */
    mouseClick : function (event) {
        var eventData = this.createImageEventData ({
                type : "click",
                clientX : event.clientX,
                clientY : event.clientY
            });
        this.fireEvent ("click", eventData);
        /*
        if (!eventData.defaultPrevented) {
            if (!this.parameters.touchUI) {
                return;
            }
            if (this.dragged) {
                return;
            }
            
            var zoomOutBorderSize = this.getTouchAreaBaseSize ();
            var zoomInHotspotSize = this.getTouchAreaBaseSize ();
            
            if (Math.abs (clickPos.x) > (this.container.clientWidth / 2 - zoomOutBorderSize) || Math.abs (clickPos.y) > (this.container.clientHeight / 2 - zoomOutBorderSize)) {
                this.flyTo (this.x, this.y, this.zoom - 0.5);
            } else {
                var newZoom = this.zoom;
                if (Math.abs (clickPos.x) < zoomInHotspotSize && Math.abs (clickPos.y) < zoomInHotspotSize) {
                    newZoom += 0.5;
                }
                var scale = Math.pow (2, this.zoom);
                clickPos.x /= scale;
                clickPos.y /= scale;
                this.flyTo (this.x + clickPos.x, this.y + clickPos.y, newZoom);
            }
        }
        */
    },
    
    /**
     * Briefly shows the touch ui zones. See the {@link bigshot.ImageParameters#touchUI}
     * documentation for an explanation of the touch ui.
     * 
     * @public
     * @deprecated All common touch gestures are supported by default.
     * @see bigshot.ImageParameters#touchUI
     * @param {int} [delay] milliseconds before fading out
     * @param {int} [fadeOut] milliseconds to fade out the zone overlays in
     */
    showTouchUI : function (delay, fadeOut) {
        if (!delay) {
            delay = 2500;
        }
        if (!fadeOut) {
            fadeOut = 1000;
        }
        
        var zoomOutBorderSize = this.getTouchAreaBaseSize ();
        var zoomInHotspotSize = this.getTouchAreaBaseSize ();
        var centerX = this.container.clientWidth / 2;
        var centerY = this.container.clientHeight / 2;
        
        var frameDiv = document.createElement ("div");
        frameDiv.style.position = "absolute";
        frameDiv.style.zIndex = "9999";
        frameDiv.style.opacity = 0.9;
        frameDiv.style.width = this.container.clientWidth + "px";
        frameDiv.style.height = this.container.clientHeight + "px";
        
        var centerSpotAnchor = document.createElement ("div");
        centerSpotAnchor.style.position = "absolute";
        
        var centerSpot = document.createElement ("div");
        centerSpot.style.position = "relative";
        centerSpot.style.background = "black";
        centerSpot.style.textAlign = "center";
        centerSpot.style.top = (centerY - zoomInHotspotSize) + "px";
        centerSpot.style.left = (centerX - zoomInHotspotSize) + "px";
        centerSpot.style.width = (2 * zoomInHotspotSize) + "px";
        centerSpot.style.height = (2 * zoomInHotspotSize) + "px";
        
        frameDiv.appendChild (centerSpotAnchor);
        centerSpotAnchor.appendChild (centerSpot);
        centerSpot.innerHTML = "<span style='display:inline-box; position:relative; vertical-align:middle; font-size: 20pt; top: 10pt; color:white'>ZOOM IN</span>";
        
        var zoomOutBorderAnchor = document.createElement ("div");
        zoomOutBorderAnchor.style.position = "absolute";
        
        var zoomOutBorder = document.createElement ("div");
        zoomOutBorder.style.position = "relative";
        zoomOutBorder.style.border = zoomOutBorderSize + "px solid black";
        zoomOutBorder.style.top = "0px";
        zoomOutBorder.style.left = "0px";
        zoomOutBorder.style.textAlign = "center";
        zoomOutBorder.style.width = this.container.clientWidth + "px";
        zoomOutBorder.style.height = this.container.clientHeight + "px";
        zoomOutBorder.style.MozBoxSizing = 
            zoomOutBorder.style.boxSizing = 
            zoomOutBorder.style.WebkitBoxSizing = 
            "border-box";
        
        zoomOutBorder.innerHTML = "<span style='position:relative; font-size: 20pt; top: -25pt; color:white'>ZOOM OUT</span>";
        
        zoomOutBorderAnchor.appendChild (zoomOutBorder);
        frameDiv.appendChild (zoomOutBorderAnchor);
        
        this.container.appendChild (frameDiv);
        
        var that = this;
        var opacity = 0.9;
        var fadeOutSteps = fadeOut / 50;
        if (fadeOutSteps < 1) {
            fadeOutSteps = 1;
        }
        var iter = function () {
            opacity = opacity - (0.9 / fadeOutSteps);
            if (opacity < 0.0) {
                that.container.removeChild (frameDiv);
            } else {
                frameDiv.style.opacity = opacity;
                setTimeout (iter, 50);
            }
        };
        setTimeout (iter, delay);
    },
    
    /**
     * Forces exit from full screen mode, if we're there.
     * @public
     */
    exitFullScreen : function () {
        if (this.fullScreenHandler) {
            this.removeEventListeners ();
            this.fullScreenHandler.close ();
            this.addEventListeners ();
            this.fullScreenHandler = null;
            return;
        }
    },
    
    /**
     * Maximizes the image to cover the browser viewport.
     * The container div is removed from its parent node upon entering 
     * full screen mode. When leaving full screen mode, the container
     * is appended to its old parent node. To avoid rearranging the
     * nodes, wrap the container in an extra div.
     *
     * <p>For unknown reasons (probably security), browsers will
     * not let you open a window that covers the entire screen.
     * Even when specifying "fullscreen=yes", all you get is a window
     * that has a title bar and only covers the desktop (not any task
     * bars or the like). For now, this is the best that I can do,
     * but should the situation change I'll update this to be
     * full-screen<i>-ier</i>.
     * @public
     */
    fullScreen : function (onClose) {
        if (this.fullScreenHandler) {
            return;
        }
        
        var message = document.createElement ("div");
        message.style.position = "absolute";
        message.style.fontSize = "16pt";
        message.style.top = "128px";
        message.style.width = "100%";
        message.style.color = "white";
        message.style.padding = "16px";
        message.style.zIndex = "9999";
        message.style.textAlign = "center";
        message.style.opacity = "0.75";
        message.innerHTML = "<span style='border-radius: 16px; -moz-border-radius: 16px; padding: 16px; padding-left: 32px; padding-right: 32px; background:black'>Press Esc to exit full screen mode.</span>";
        
        var that = this;
        
        this.fullScreenHandler = new bigshot.FullScreen (this.container);
        this.fullScreenHandler.restoreSize = true;
        
        this.fullScreenHandler.addOnResize (function () {
                if (that.fullScreenHandler && that.fullScreenHandler.isFullScreen) {
                    that.container.style.width = window.innerWidth + "px";
                    that.container.style.height = window.innerHeight + "px";                
                }
                that.onresize ();
            });
        
        this.fullScreenHandler.addOnClose (function () {
                if (message.parentNode) {
                    try {
                        div.removeChild (message);
                    } catch (x) {
                    }
                }
                that.fullScreenHandler = null;
            });
        
        if (onClose) {
            this.fullScreenHandler.addOnClose (function () {
                    onClose ();
                });
        }
        
        this.removeEventListeners ();
        this.fullScreenHandler.open ();
        this.addEventListeners ();
        if (this.fullScreenHandler.getRootElement ()) {
            this.fullScreenHandler.getRootElement ().appendChild (message);
            
            setTimeout (function () {
                    var opacity = 0.75;
                    var iter = function () {
                        opacity -= 0.02;
                        if (message.parentNode) {
                            if (opacity <= 0) {
                                try {
                                    div.removeChild (message);
                                } catch (x) {}
                            } else {
                                message.style.opacity = opacity;
                                setTimeout (iter, 20);
                            }
                        }
                    };
                    setTimeout (iter, 20);
                }, 3500);
        }
        
        return function () {
            that.fullScreenHandler.close ();
        };        
    },
    
    /**
     * Unregisters event handlers and other page-level hooks. The client need not call this
     * method unless bigshot images are created and removed from the page
     * dynamically. In that case, this method must be called when the client wishes to
     * free the resources allocated by the image. Otherwise the browser will garbage-collect
     * all resources automatically.
     * @public
     */
    dispose : function () {
        this.browser.unregisterListener (window, "resize", this.onresizeHandler, false);
        this.removeEventListeners ();
    }
};

/**
 * Fired when the user double-clicks on the image
 *
 * @name bigshot.ImageBase#dblclick
 * @event
 * @param {bigshot.ImageEvent} event the event object
 */

/**
 * Fired when the user clicks on (but does not drag) the image
 *
 * @name bigshot.ImageBase#click
 * @event
 * @param {bigshot.ImageEvent} event the event object
 */

bigshot.Object.extend (bigshot.ImageBase, bigshot.EventDispatcher);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */


/**
 * Creates a new tiled image viewer. (Note: See {@link bigshot.ImageBase#dispose} for important information.)
 *
 * @example
 * var bsi = new bigshot.Image (
 *     new bigshot.ImageParameters ({
 *         basePath : "/bigshot.php?file=myshot.bigshot",
 *         fileSystemType : "archive",
 *         container : document.getElementById ("bigshot_div")
 *     }));
 *
 * @param {bigshot.ImageParameters} parameters the image parameters. Required fields are: <code>basePath</code> and <code>container</code>.
 * If you intend to use the archive filesystem, you need to set the <code>fileSystemType</code> to <code>"archive"</code>
 * as well.
 * @see bigshot.ImageBase#dispose
 * @class A tiled, zoomable image viewer.
 *
 * <h3 id="creating-a-wrapping-image">Creating a Wrapping Image</h3>
 *
 * <p>If you have set the wrapX or wrapY parameters in the {@link bigshot.ImageParameters}, the 
 * image must be an integer multiple of the tile size at the desired minimum zoom level, otherwise
 * there will be a gap at the wrap point:
 *
 * <p>The way to figure out the proper input size is this:
 *
 * <ol>
 * <li><p>Decide on a tile size and call this <i>tileSize</i>.</p></li>
 * <li><p>Decide on a minimum integer zoom level, and call this <i>minZoom</i>.</p></li>
 * <li><p>Compute <i>tileSize * 2<sup>-minZoom</sup></i>, call this <i>S</i>.</p></li>
 * <li><p>The source image size along the wrapped axis must be evenly divisible by <i>S</i>.</p></li>
 * </ol>
 *
 * <p>An example:</p>
 *
 * <ol>
 * <li><p>I have an image that is 23148x3242 pixels.</p></li>
 * <li><p>I chose 256x256 pixel tiles: <i>tileSize = 256</i>.</p></li>
 * <li><p>When displaying the image, I want the user to be able to zoom out so that the 
 * whole image is less than or equal to 600 pixels tall. Since the image is 3242 pixels 
 * tall originally, I will need a <i>minZoom</i> of -3. A <i>minZoom</i> of -2 would only let me
 * zoom out to 1/4 (2<sup>-2</sup>), or an image that is 810 pixels tall. A <i>minZoom</i> of -3, however lets me
 * zoom out to 1/8 (2<sup>-3</sup>), or an image that is 405 pixels tall. Thus: <i>minZoom = -3</i></p></li>
 * <li><p>Computing <i>S</i> gives: <i>S = 256 * 2<sup>3</sup> = 256 * 8 = 2048</i></p></li>
 * <li><p>I want it to wrap along the X axis. Therefore I may have to adjust the width, 
 * currently 23148 pixels.</p></li>
 * <li><p>Rounding 23148 down to the nearest multiple of 2048 gives 22528. (23148 divided by 2048 is 11.3, and 11 times 2048 is 22528.)</p></li>
 * <li><p>I will shrink my source image to be 22528 pixels wide before building the image pyramid,
 * and I will set the <code>minZoom</code> parameter to -3 in the {@link bigshot.ImageParameters} when creating
 * the image. (I will also set <code>wrapX</code> to <code>true</code>.)</p></li>
 * </ol>
 * 
 * @augments bigshot.ImageBase
 */     
bigshot.Image = function (parameters) {
    bigshot.setupFileSystem (parameters);
    parameters.merge (parameters.fileSystem.getDescriptor (), false);
    
    bigshot.ImageBase.call (this, parameters);
}    

bigshot.Image.prototype = {
    setupLayers : function () {
        var that = this;
        this.thisTileCache = new bigshot.ImageTileCache (function () {
                that.layout ();     
            }, null, this.parameters);
        
        this.addLayer (
            new bigshot.TileLayer (this, this.parameters, 0, 0, this.thisTileCache)
        );
    }
};

bigshot.Object.extend (bigshot.Image, bigshot.ImageBase);

/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new HTML element layer. The layer must be added to the image using
 * {@link bigshot.ImageBase#addLayer}.
 *
 * @class A layer consisting of a single HTML element that is moved and scaled to cover
 * the layer.
 * @example
 * var image = new bigshot.Image (...);
 * image.addLayer (
 *     new bigshot.HTMLElementLayer (this, this.imgElement, this.parameters.width, this.parameters.height)
 * );
 * @param {bigshot.ImageBase} image the image this hotspot layer will be part of
 * @param {HTMLElement} element the element to present in this layer
 * @param {int} width the width, in image pixels (display size at zoom level 0), of the HTML element
 * @param {int} height the height, in image pixels (display size at zoom level 0), of the HTML element
 * @augments bigshot.Layer
 */
bigshot.HTMLElementLayer = function (image, element, width, height) {
    this.hotspots = new Array ();
    this.browser = new bigshot.Browser ();
    this.image = image;
    this.container = image.createLayerContainer ();
    this.parentContainer = image.getContainer ();
    this.element = element;
    this.parentContainer.appendChild (element);
    this.w = width;
    this.h = height;
    this.resize (0, 0);
}

bigshot.HTMLElementLayer.prototype = {
    
    getContainer : function () {
        return this.container;
    },
    
    resize : function (w, h) {
        this.container.style.width = this.parentContainer.clientWidth + "px";
        this.container.style.height = this.parentContainer.clientHeight + "px";
    },
    
    layout : function (zoom, x0, y0, tx0, ty0, size, stride, opacity) {
        var zoomFactor = Math.pow (2, this.image.getZoom ());
        x0 -= stride * tx0;
        y0 -= stride * ty0;
        
        this.element.style.top = y0 + "px";
        this.element.style.left = x0 + "px";
        this.element.style.width = (this.w * zoomFactor) + "px";
        this.element.style.height = (this.h * zoomFactor) + "px";
    },
    
    setMaxTiles : function (mtx, mty) {
    }
}

bigshot.Object.validate ("bigshot.HTMLElementLayer", bigshot.Layer);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new HTML element layer. The layer must be added to the image using
 * {@link bigshot.ImageBase#addLayer}.
 *
 * @class A layer consisting of a single HTML element that is moved and scaled to cover
 * the layer.
 * @example
 * var image = new bigshot.Image (...);
 * image.addLayer (
 *     new bigshot.HTMLElementLayer (this, this.imgElement, this.parameters.width, this.parameters.height)
 * );
 * @param {bigshot.ImageBase} image the image this hotspot layer will be part of
 * @param {HTMLElement} element the element to present in this layer
 * @param {int} width the width, in image pixels (display size at zoom level 0), of the HTML element
 * @param {int} height the height, in image pixels (display size at zoom level 0), of the HTML element
 * @augments bigshot.Layer
 */
bigshot.HTMLDivElementLayer = function (image, element, width, height, wrapX, wrapY) {
    this.wrapX = wrapX;
    this.wrapY = wrapY;
    this.hotspots = new Array ();
    this.browser = new bigshot.Browser ();
    this.image = image;
    this.container = image.createLayerContainer ();
    this.parentContainer = image.getContainer ();
    this.element = element;
    this.parentContainer.appendChild (element);
    this.w = width;
    this.h = height;
    this.resize (0, 0);
}

bigshot.HTMLDivElementLayer.prototype = {
    
    getContainer : function () {
        return this.container;
    },
    
    resize : function (w, h) {
        this.container.style.width = this.parentContainer.clientWidth + "px";
        this.container.style.height = this.parentContainer.clientHeight + "px";
    },
    
    layout : function (zoom, x0, y0, tx0, ty0, size, stride, opacity) {
        var zoomFactor = Math.pow (2, this.image.getZoom ());
        x0 -= stride * tx0;
        y0 -= stride * ty0;
        
        var imW = (this.w * zoomFactor);
        var imH = (this.h * zoomFactor);
        
        this.element.style.backgroundSize = imW + "px " + imH + "px";
            
        var bposX = "0px";
        var bposY = "0px";
        
        if (this.wrapY) {
            this.element.style.top = "0px";
            this.element.style.height = (this.parentContainer.clientHeight) + "px";
            bposY = y0 + "px";
        } else {
            this.element.style.top = y0 + "px";
            this.element.style.height = imH + "px";
        }
        
        if (this.wrapX) {
            this.element.style.left = "0px";
            this.element.style.width = (this.parentContainer.clientWidth) + "px";            
            bposX = x0 + "px";
        } else {
            this.element.style.left = x0 + "px";
            this.element.style.width = imW + "px";
        }
        
        this.element.style.backgroundPosition = bposX + " " + bposY;
    },
    
    setMaxTiles : function (mtx, mty) {
    }
}

bigshot.Object.validate ("bigshot.HTMLDivElementLayer", bigshot.Layer);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */


/**
 * Creates a new image viewer. (Note: See {@link bigshot.SimpleImage#dispose} for important information.)
 *
 * @example
 * var bsi = new bigshot.SimpleImage (
 *     new bigshot.ImageParameters ({
 *         basePath : "myimage.jpg",
 *         width : 681,
 *         height : 1024,
 *         container : document.getElementById ("bigshot_div")
 *     }));
 *
 * @param {bigshot.ImageParameters} parameters the image parameters. Required fields are: <code>container</code>. 
 * If the <code>imgElement</code> parameter is not given, then <code>basePath</code>, <code>width</code> and <code>height</code> are also required. The
 * following parameters are not supported and should be left as defaults: <code>fileSystem</code>, <code>fileSystemType</code>, 
 * <code>maxTextureMagnification</code> and <code>tileSize</code>. <code>wrapX</code> and <code>wrapY</code> may only be used if the imgElement is <b>not</b>
 * set.
 * 
 * @param {HTMLImageElement} [imgElement] an img element to use. The element should have <code>style.position = "absolute"</code>.
 * @see bigshot.ImageBase#dispose
 * @class A zoomable image viewer.
 * @augments bigshot.ImageBase
 */     
bigshot.SimpleImage = function (parameters, imgElement) {
    parameters.merge ({
            fileSystem : null,
            fileSystemType : "simple",
            maxTextureMagnification : 1.0,
            tileSize : 1024
        }, true);
    
    if (imgElement) {
        parameters.merge ({
                width : imgElement.width,
                height : imgElement.height
            });
        this.imgElement = imgElement;
    } else {
        if (parameters.width == 0 || parameters.height == 0) {
            throw new Error ("No imgElement and missing width or height in ImageParameters");
        }
    }
    bigshot.setupFileSystem (parameters);
    
    bigshot.ImageBase.call (this, parameters);
}    

bigshot.SimpleImage.prototype = {
    setupLayers : function () {
        if (!this.imgElement) {
            /*
            this.imgElement = document.createElement ("img");
            this.imgElement.src = this.parameters.basePath;
            this.imgElement.style.position = "absolute";
            */
            this.imgElement = document.createElement ("div");
            this.imgElement.style.backgroundImage = "url('" + this.parameters.basePath + "')";
            this.imgElement.style.position = "absolute";
            if (!this.parameters.wrapX && !this.parameters.wrapY) {
                this.imgElement.style.backgroundRepeat = "no-repeat";
            } else if (this.parameters.wrapX && !this.parameters.wrapY) {
                this.imgElement.style.backgroundRepeat = "repeat-x";
            } else if (!this.parameters.wrapX && this.parameters.wrapY) {
                this.imgElement.style.backgroundRepeat = "repeat-y";
            } else if (this.parameters.wrapX && this.parameters.wrapY) {
                this.imgElement.style.backgroundRepeat = "repeat";
            }
        }
        
        this.addLayer (
            new bigshot.HTMLDivElementLayer (this, this.imgElement, this.parameters.width, this.parameters.height, this.parameters.wrapX, this.parameters.wrapY)
        );
    }
};

bigshot.Object.extend (bigshot.SimpleImage, bigshot.ImageBase);

/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Abstract filesystem definition.
 *
 * @class Abstract filesystem definition.
 */
bigshot.FileSystem = function () {
}

bigshot.FileSystem.prototype = {
    /**
     * Returns the URL filename for the given filesystem entry.
     *
     * @param {String} name the entry name
     */
    getFilename : function (name) {},
    
    /**
     * Returns the entry filename for the given tile.
     * 
     * @param {int} tileX the column of the tile
     * @param {int} tileY the row of the tile
     * @param {int} zoomLevel the zoom level
     */
    getImageFilename : function (tileX, tileY, zoomLevel) {},
    
    /**
     * Sets an optional prefix that is prepended, along with a forward
     * slash ("/"), to all names.
     *
     * @param {String} prefix the prefix
     */
    setPrefix : function (prefix) {},
    
    /**
     * Returns an image descriptor object from the descriptor file.
     *
     * @return a descriptor object
     */
    getDescriptor : function () {},
    
    /**
     * Returns the poster URL filename. For Bigshot images this is
     * typically the URL corresponding to the entry "poster.jpg", 
     * but for other filesystems it can be different.
     */
    getPosterFilename : function () {}
};

/**
 * Sets up a filesystem instance in the given parameters object, if none exist.
 * If the {@link bigshot.ImageParameters#fileSystem} member isn't set, the 
 * {@link bigshot.ImageParameters#fileSystemType} member is used to create a new 
 * {@link bigshot.FileSystem} instance and set it.
 *
 * @param {bigshot.ImageParameters or bigshot.VRPanoramaParameters or bigshot.ImageCarouselPanoramaParameters} parameters the parameters object to populate
 */
bigshot.setupFileSystem = function (parameters) {
    if (!parameters.fileSystem) {
        if (parameters.fileSystemType == "archive") {
            parameters.fileSystem = new bigshot.ArchiveFileSystem (parameters);
        } else if (parameters.fileSystemType == "dzi") {
            parameters.fileSystem = new bigshot.DeepZoomImageFileSystem (parameters);
        } else if (parameters.fileSystemType == "simple") {
            parameters.fileSystem = new bigshot.SimpleFileSystem (parameters);
        } else {
            parameters.fileSystem = new bigshot.FolderFileSystem (parameters);
        }
    }
}
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new instance of a filesystem adapter for the SimpleImage class.
 * 
 * @class Filesystem adapter for bigshot.SimpleImage. This class is not
 * supposed to be used outside of the {@link bigshot.SimpleImage} class.
 * @param {bigshot.ImageParameters} parameters the associated image parameters
 * @augments bigshot.FileSystem
 * @see bigshot.SimpleImage
 */     
bigshot.SimpleFileSystem = function (parameters) {
    this.parameters = parameters;
};


bigshot.SimpleFileSystem.prototype = { 
    getDescriptor : function () {
        return {};
    },
    
    getPosterFilename : function () {
        return null;
    },
    
    getFilename : function (name) {
        return null;
    },
    
    getImageFilename : function (tileX, tileY, zoomLevel) {
        return null;
    },
    
    getPrefix : function () {
        return "";
    },
    
    setPrefix : function (prefix) {
        this.prefix = prefix;
    }
}

bigshot.Object.validate ("bigshot.SimpleFileSystem", bigshot.FileSystem);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new instance of a folder-based filesystem adapter.
 *
 * @augments bigshot.FileSystem
 * @class Folder-based filesystem.
 * @param {bigshot.ImageParameters|bigshot.VRPanoramaParameters} parameters the associated image parameters
 * @constructor
 */
bigshot.FolderFileSystem = function (parameters) {
    this.prefix = null;
    this.suffix = "";
    this.parameters = parameters;
}


bigshot.FolderFileSystem.prototype = {    
    getDescriptor : function () {
        this.browser = new bigshot.Browser ();
        var req = this.browser.createXMLHttpRequest ();
        
        req.open("GET", this.getFilename ("descriptor"), false);   
        req.send(null); 
        var descriptor = {};
        if(req.status == 200) {
            var substrings = req.responseText.split (":");
            for (var i = 0; i < substrings.length; i += 2) {
                if (substrings[i] == "suffix") {
                    descriptor[substrings[i]] = substrings[i + 1];
                } else {
                    descriptor[substrings[i]] = parseInt (substrings[i + 1]);
                }
            }
            this.suffix = descriptor.suffix;
            return descriptor;
        } else {
            throw new Error ("Unable to find descriptor.");
        }
    },
    
    getPosterFilename : function () {
        return this.getFilename ("poster" + this.suffix);
    },
    
    setPrefix : function (prefix) {
        this.prefix = prefix;
    },
    
    getPrefix : function () {
        if (this.prefix) {
            return this.prefix + "/";
        } else {
            return "";
        }
    },
    
    getFilename : function (name) {
        return this.parameters.basePath + "/" + this.getPrefix () + name;
    },
    
    getImageFilename : function (tileX, tileY, zoomLevel) {
        var key = (-zoomLevel) + "/" + tileX + "_" + tileY + this.suffix;
        return this.getFilename (key);
    }
};

bigshot.Object.validate ("bigshot.FolderFileSystem", bigshot.FileSystem);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new instance of a Deep Zoom Image folder-based filesystem adapter.
 *
 * @augments bigshot.FileSystem
 * @class A Deep Zoom Image filesystem.
 * @param {bigshot.ImageParameters|bigshot.VRPanoramaParameters} parameters the associated image parameters
 * @constructor
 */
bigshot.DeepZoomImageFileSystem = function (parameters) {
    this.prefix = "";
    this.suffix = "";
    
    this.DZ_NAMESPACE = "http://schemas.microsoft.com/deepzoom/2009";
    this.fullZoomLevel = 0;
    this.posterName = "";
    this.parameters = parameters;
}

bigshot.DeepZoomImageFileSystem.prototype = {    
    getDescriptor : function () {
        var descriptor = {};
        
        var xml = this.parameters.dataLoader.loadXml (this.parameters.basePath + this.prefix + ".xml", false);
        var image = xml.getElementsByTagName ("Image")[0];
        var size = xml.getElementsByTagName ("Size")[0];
        descriptor.width = parseInt (size.getAttribute ("Width"));
        descriptor.height = parseInt (size.getAttribute ("Height"));
        descriptor.tileSize = parseInt (image.getAttribute ("TileSize"));
        descriptor.overlap = parseInt (image.getAttribute ("Overlap"));
        descriptor.suffix = "." + image.getAttribute ("Format")
        descriptor.posterSize = descriptor.tileSize;
        
        this.suffix = descriptor.suffix;
        this.fullZoomLevel = Math.ceil (Math.log (Math.max (descriptor.width, descriptor.height)) / Math.LN2);
        
        descriptor.minZoom = -this.fullZoomLevel;
        var posterZoomLevel = Math.ceil (Math.log (descriptor.tileSize) / Math.LN2);
        this.posterName = this.getImageFilename (0, 0, posterZoomLevel - this.fullZoomLevel);
        return descriptor;
    },
    
    setPrefix : function (prefix) {
        this.prefix = prefix;
    },
    
    getPosterFilename : function () {
        return this.posterName;
    },
    
    getFilename : function (name) {
        return this.parameters.basePath + this.prefix + "/" + name;
    },
    
    getImageFilename : function (tileX, tileY, zoomLevel) {
        var dziZoomLevel = this.fullZoomLevel + zoomLevel;
        var key = dziZoomLevel + "/" + tileX + "_" + tileY + this.suffix;
        return this.getFilename (key);
    }
};
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new instance of a <code>.bigshot</code> archive filesystem adapter.
 * 
 * @class Bigshot archive filesystem.
 * @param {bigshot.ImageParameters|bigshot.VRPanoramaParameters} parameters the associated image parameters
 * @augments bigshot.FileSystem
 * @constructor
 */     
bigshot.ArchiveFileSystem = function (parameters) {
    this.indexSize = 0;
    this.offset = 0;
    this.index = {};
    this.prefix = "";
    this.suffix = "";
    this.parameters = parameters;
    
    var browser = new bigshot.Browser ();
    var req = browser.createXMLHttpRequest ();
    req.open("GET", this.parameters.basePath + "&start=0&length=24&type=text/plain", false);   
    req.send(null);  
    if(req.status == 200) {
        if (req.responseText.substring (0, 7) != "BIGSHOT") {
            alert ("\"" + this.parameters.basePath + "\" is not a valid bigshot file");
            return;
        }
        this.indexSize = parseInt (req.responseText.substring (8), 16);
        this.offset = this.indexSize + 24;
        
        req.open("GET", this.parameters.basePath + "&type=text/plain&start=24&length=" + this.indexSize, false);   
        req.send(null);  
        if(req.status == 200) {
            var substrings = req.responseText.split (":");
            for (var i = 0; i < substrings.length; i += 3) {
                this.index[substrings[i]] = {
                    start : parseInt (substrings[i + 1]) + this.offset,
                    length : parseInt (substrings[i + 2])
                };
            }
        } else {
            alert ("The index of \"" + this.parameters.basePath + "\" could not be loaded: " + req.status);
        }
    } else {
        alert ("The header of \"" + this.parameters.basePath + "\" could not be loaded: " + req.status);
    }
};


bigshot.ArchiveFileSystem.prototype = { 
    getDescriptor : function () {
        this.browser = new bigshot.Browser ();
        var req = this.browser.createXMLHttpRequest ();
        
        req.open("GET", this.getFilename ("descriptor"), false);   
        req.send(null); 
        var descriptor = {};
        if(req.status == 200) {
            var substrings = req.responseText.split (":");
            for (var i = 0; i < substrings.length; i += 2) {
                if (substrings[i] == "suffix") {
                    descriptor[substrings[i]] = substrings[i + 1];
                } else {
                    descriptor[substrings[i]] = parseInt (substrings[i + 1]);
                }
            }
            this.suffix = descriptor.suffix;
            return descriptor;
        } else {
            throw new Error ("Unable to find descriptor.");
        }
    },
    
    getPosterFilename : function () {
        return this.getFilename ("poster" + this.suffix);
    },
    
    getFilename : function (name) {
        name = this.getPrefix () + name;
        if (!this.index[name] && console) {
            console.log ("Can't find " + name);
        }
        var f = this.parameters.basePath + "&start=" + this.index[name].start + "&length=" + this.index[name].length;
        if (name.substring (name.length - 4) == ".jpg") {
            f = f + "&type=image/jpeg";
        } else if (name.substring (name.length - 4) == ".png") {
            f = f + "&type=image/png";
        } else {
            f = f + "&type=text/plain";
        }
        return f;
    },
    
    getImageFilename : function (tileX, tileY, zoomLevel) {
        var key = (-zoomLevel) + "/" + tileX + "_" + tileY + this.suffix;
        return this.getFilename (key);
    },
    
    getPrefix : function () {
        if (this.prefix) {
            return this.prefix + "/";
        } else {
            return "";
        }
    },
    
    setPrefix : function (prefix) {
        this.prefix = prefix;
    }
}

bigshot.Object.validate ("bigshot.ArchiveFileSystem", bigshot.FileSystem);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */
    
/**
 * @class Abstract base class.
 */
bigshot.VRTileCache = function () {
}

bigshot.VRTileCache.prototype = {
    /**
     * Returns the texture object for the given tile-x, tile-y and zoom level.
     * The return type is dependent on the renderer. The WebGL renderer, for example
     * uses a tile cache that returns WebGL textures, while the CSS3D renderer
     * returns HTML img or canvas elements.
     */
    getTexture : function (tileX, tileY, zoomLevel) {},
    
    /**
     * Purges the cache of old entries.
     *
     * @type void
     */
    purge : function () {},
    
    /**
     * Disposes the cache and all its entries.
     *
     * @type void
     */
    dispose : function () {}
}
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */
    
/**
 * @class A VR tile cache backed by a {@link bigshot.ImageTileCache}.
 * @augments bigshot.VRTileCache
 */
bigshot.ImageVRTileCache = function (onloaded, onCacheInit, parameters) {
    this.imageTileCache = new bigshot.ImageTileCache (onloaded, onCacheInit, parameters);
    
    // Keep the imageTileCache from wrapping around.
    this.imageTileCache.setMaxTiles (999999, 999999);
}

bigshot.ImageVRTileCache.prototype = {
    getTexture : function (tileX, tileY, zoomLevel) {
        var res = this.imageTileCache.getImage (tileX, tileY, zoomLevel);
        return res;
    },
    
    purge : function () {
        this.imageTileCache.resetUsed ();
    },
    
    dispose : function () {
        
    }
}

bigshot.Object.validate ("bigshot.ImageVRTileCache", bigshot.VRTileCache);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new cache instance.
 *
 * @class Tile texture cache for a {@link bigshot.VRFace}.
 * @augments bigshot.VRTileCache
 * @param {function()} onLoaded function that is called whenever a texture tile has been loaded
 * @param {function()} onCacheInit function that is called when the texture cache is fully initialized
 * @param {bigshot.VRPanoramaParameters} parameters image parameters
 * @param {bigshot.WebGL} _webGl WebGL instance to use
 */
bigshot.TextureTileCache = function (onLoaded, onCacheInit, parameters, _webGl) {
    this.parameters = parameters;
    this.webGl = _webGl;
    
    /**
     * Reduced-resolution preview of the full image.
     * Loaded from the "poster" image created by 
     * MakeImagePyramid
     *
     * @private
     * @type HTMLImageElement
     */
    this.fullImage = parameters.dataLoader.loadImage (parameters.fileSystem.getPosterFilename (), onCacheInit);
    
    /**
     * Maximum number of WebGL textures in the cache. This is the
     * "L1" cache.
     *
     * @private
     * @type int
     */
    this.maxTextureCacheSize = 512;
    
    /**
     * Maximum number of HTMLImageElement images in the cache. This is the
     * "L2" cache.
     *
     * @private
     * @type int
     */
    this.maxImageCacheSize = 2048;
    this.cachedTextures = {};
    this.cachedImages = {};
    this.requestedImages = {};
    this.lastOnLoadFiredAt = 0;
    this.imageRequests = 0;
    this.partialImageSize = parameters.tileSize / 8;
    this.imageLruMap = new bigshot.LRUMap ();
    this.textureLruMap = new bigshot.LRUMap ();
    this.onLoaded = onLoaded;
    this.browser = new bigshot.Browser ();
    this.disposed = false;
}

bigshot.TextureTileCache.prototype = {
    
    getPartialTexture : function (tileX, tileY, zoomLevel) {
        if (this.fullImage.complete) {
            var canvas = document.createElement ("canvas");
            if (!canvas["width"]) {
                return null;
            }
            canvas.width = this.partialImageSize;
            canvas.height = this.partialImageSize;
            var ctx = canvas.getContext ("2d"); 
            
            var posterScale = this.parameters.posterSize / Math.max (this.parameters.width, this.parameters.height);
            
            var posterWidth = Math.floor (posterScale * this.parameters.width);
            var posterHeight = Math.floor (posterScale * this.parameters.height);
            
            var tileSizeAtZoom = posterScale * (this.parameters.tileSize - this.parameters.overlap) / Math.pow (2, zoomLevel);    
            var sx = Math.floor (tileSizeAtZoom * tileX);
            var sy = Math.floor (tileSizeAtZoom * tileY);
            var sw = Math.floor (tileSizeAtZoom);
            var sh = Math.floor (tileSizeAtZoom);
            var dw = this.partialImageSize + 2;
            var dh = this.partialImageSize + 2;
            
            if (sx + sw > posterWidth) {
                sw = posterWidth - sx;
                dw = this.partialImageSize * (sw / Math.floor (tileSizeAtZoom));
            }
            if (sy + sh > posterHeight) {
                sh = posterHeight - sy;
                dh = this.partialImageSize * (sh / Math.floor (tileSizeAtZoom));
            }
            
            ctx.drawImage (this.fullImage, sx, sy, sw, sh, -1, -1, dw, dh);
            
            return this.webGl.createImageTextureFromImage (canvas, this.parameters.textureMinFilter, this.parameters.textureMagFilter);
        } else {
            return null;
        }
    },
    
    setCachedTexture : function (key, newTexture) {
        if (this.cachedTextures[key] != null) {
            this.webGl.deleteTexture (this.cachedTextures[key]);
        }
        this.cachedTextures[key] = newTexture;
    },
        
    getTexture : function (tileX, tileY, zoomLevel) {
        var key = this.getImageKey (tileX, tileY, zoomLevel);
        this.textureLruMap.access (key);
        this.imageLruMap.access (key);
        
        if (this.cachedTextures[key]) {
            return this.cachedTextures[key];
        } else if (this.cachedImages[key]) {
            this.setCachedTexture (key, this.webGl.createImageTextureFromImage (this.cachedImages[key], this.parameters.textureMinFilter, this.parameters.textureMagFilter));
            return this.cachedTextures[key];
        } else {
            this.requestImage (tileX, tileY, zoomLevel);
            var partial = this.getPartialTexture (tileX, tileY, zoomLevel);
            if (partial) {
                this.setCachedTexture (key, partial);
            }
            return partial;
        }
    },
    
    requestImage : function (tileX, tileY, zoomLevel) {
        var key = this.getImageKey (tileX, tileY, zoomLevel);
        if (!this.requestedImages[key]) {
            this.imageRequests++;
            var that = this;
            this.parameters.dataLoader.loadImage (this.getImageFilename (tileX, tileY, zoomLevel), function (tile) {
                    if (that.disposed) {
                        return;
                    }
                    that.cachedImages[key] = tile;
                    that.setCachedTexture (key, that.webGl.createImageTextureFromImage (tile, that.parameters.textureMinFilter, that.parameters.textureMagFilter));
                    delete that.requestedImages[key];
                    that.imageRequests--;
                    var now = new Date();
                    if (that.imageRequests == 0 || now.getTime () > (that.lastOnLoadFiredAt + 50)) {
                        that.lastOnLoadFiredAt = now.getTime ();
                        that.onLoaded ();
                    }
                });
            this.requestedImages[key] = true;
        }            
    },
    
    purge : function () {
        var that = this;
        this.purgeCache (this.textureLruMap, this.cachedTextures, this.maxTextureCacheSize, function (leastUsedKey) {
                that.webGl.deleteTexture (that.cachedTextures[leastUsedKey]);
            });
        this.purgeCache (this.imageLruMap, this.cachedImages, this.maxImageCacheSize, function (leastUsedKey) {
            });
    },
    
    purgeCache : function (lruMap, cache, maxCacheSize, onEvict) {
        for (var i = 0; i < 64; ++i) {
            if (lruMap.getSize () > maxCacheSize) {
                var leastUsed = lruMap.leastUsed ();
                lruMap.remove (leastUsed);
                if (onEvict) {
                    onEvict (leastUsed);
                }                    
                delete cache[leastUsed];
            } else {
                break;
            }
        }
    },
    
    getImageKey : function (tileX, tileY, zoomLevel) {
        return "I" + tileX + "_" + tileY + "_" + zoomLevel;
    },
    
    getImageFilename : function (tileX, tileY, zoomLevel) {
        var f = this.parameters.fileSystem.getImageFilename (tileX, tileY, zoomLevel);
        return f;
    },
    
    dispose : function () {
        this.disposed = true;
        for (var k in this.cachedTextures) {
            this.webGl.deleteTexture (this.cachedTextures[k]);
        }
    }
};


bigshot.Object.validate ("bigshot.TextureTileCache", bigshot.VRTileCache);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new VR cube face.
 *
 * @class a VR cube face. The {@link bigshot.VRPanorama} instance holds
 * six of these.
 *
 * @param {bigshot.VRPanorama} owner the VR panorama this face is part of.
 * @param {String} key the identifier for the face. "f" is front, "b" is back, "u" is
 * up, "d" is down, "l" is left and "r" is right.
 * @param {bigshot.Point3D} topLeft_ the top-left corner of the quad.
 * @param {number} width_ the length of the sides of the face, expressed in multiples of u and v.
 * @param {bigshot.Point3D} u basis vector going from the top left corner along the top edge of the face
 * @param {bigshot.Point3D} v basis vector going from the top left corner along the left edge of the face
 */
bigshot.VRFace = function (owner, key, topLeft_, width_, u, v, onLoaded) {
    var that = this;
    this.owner = owner;
    this.key = key;
    this.topLeft = topLeft_;
    this.width = width_;
    this.u = u;
    this.v = v;
    this.updated = false;
    this.parameters = new Object ();
    
    for (var k in this.owner.getParameters ()) {
        this.parameters[k] = this.owner.getParameters ()[k];
    }
    
    bigshot.setupFileSystem (this.parameters);
    this.parameters.fileSystem.setPrefix ("face_" + key);
    this.parameters.merge (this.parameters.fileSystem.getDescriptor (), false);
    
    
    /**
     * Texture cache.
     *
     * @private
     */
    this.tileCache = owner.renderer.createTileCache (function () { 
            that.updated = true;
            owner.renderUpdated (bigshot.VRPanorama.ONRENDER_TEXTURE_UPDATE);
        }, onLoaded, this.parameters);
    
    this.fullSize = this.parameters.width;
    this.overlap = this.parameters.overlap;
    this.tileSize = this.parameters.tileSize;
    
    this.minDivisions = 0;
    var fullZoom = Math.log (this.fullSize - this.overlap) / Math.LN2;
    var singleTile = Math.log (this.tileSize - this.overlap) / Math.LN2;
    this.maxDivisions = Math.floor (fullZoom - singleTile);
    this.maxTesselation = this.parameters.maxTesselation >= 0 ? this.parameters.maxTesselation : this.maxDivisions;
}

bigshot.VRFace.prototype = {
    browser : new bigshot.Browser (),
    
    dispose : function () {
        this.tileCache.dispose ();
    },
    
    /**
     * Utility function to do a multiply-and-add of a 3d point.
     *
     * @private
     * @param p {bigshot.Point3D} the point to multiply
     * @param m {number} the number to multiply the elements of p with
     * @param a {bigshot.Point3D} the point to add
     * @return p * m + a
     */
    pt3dMultAdd : function (p, m, a) {
        return {
            x : p.x * m + a.x,
            y : p.y * m + a.y,
            z : p.z * m + a.z
        };
    },
    
    /**
     * Utility function to do an element-wise multiply of a 3d point.
     *
     * @private
     * @param p {bigshot.Point3D} the point to multiply
     * @param m {number} the number to multiply the elements of p with
     * @return p * m
     */
    pt3dMult : function (p, m) {
        return {
            x : p.x * m,
            y : p.y * m,
            z : p.z * m
        };
    },
    
    /**
     * Creates a textured quad.
     *
     * @private
     */
    generateFace : function (scene, topLeft, width, tx, ty, divisions) {
        width *= this.tileSize / (this.tileSize - this.overlap);
        var texture = this.tileCache.getTexture (tx, ty, -this.maxDivisions + divisions);
        scene.addQuad (this.owner.renderer.createTexturedQuad (
                topLeft,
                this.pt3dMult (this.u, width),
                this.pt3dMult (this.v, width),
                texture
            )
        );
    },
    
    VISIBLE_NONE : 0,
    VISIBLE_SOME : 1,
    VISIBLE_ALL : 2,
    
    /**
     * Tests whether the point is in the axis-aligned rectangle.
     * 
     * @private
     * @param point the point
     * @param min top left corner of the rectangle
     * @param max bottom right corner of the rectangle
     */
    pointInRect : function (point, min, max) {
        return (point.x >= min.x && point.y >= min.y && point.x < max.x && point.y < max.y);
    },
    
    /**
     * Intersects a quadrilateral with the view frustum.
     * The test is a simple rectangle intersection of the AABB of
     * the transformed quad with the viewport.
     *
     * @private
     * @return VISIBLE_NONE, VISIBLE_SOME or VISIBLE_ALL
     */
    intersectWithView : function intersectWithView (transformed) {
        var numNull = 0;
        var tf = [];
        var tfl = transformed.length;
        for (var i = 0; i < tfl; ++i) {
            if (transformed[i] == null) {
                numNull++;
            } else {
                tf.push (transformed[i]);
            }
        }
        if (numNull == 4) {
            return this.VISIBLE_NONE;
        }
        
        var minX = tf[0].x;
        var minY = tf[0].y;
        
        var maxX = minX;
        var maxY = minY;
        
        var viewMinX = 0;
        var viewMinY = 0;
        
        var viewMaxX = this.viewportWidth;
        var viewMaxY = this.viewportHeight;
        
        var pointsInViewport = 0;
        var tl = tf.length;
        for (var i = 1; i < tl; ++i) {
            var tix = tf[i].x;
            var tiy = tf[i].y;
            
            minX = minX < tix ? minX : tix;
            minY = minY < tiy ? minY : tiy;
            
            
            maxX = maxX > tix ? maxX : tix;
            maxY = maxY > tiy ? maxY : tiy;
        }
        
        var iminX = minX > viewMinX ? minX : viewMinX;
        var iminY = minY > viewMinY ? minY : viewMinY;
        
        var imaxX = maxX < viewMaxX ? maxX : viewMaxX;
        var imaxY = maxY < viewMaxY ? maxY : viewMaxY;
        
        if (iminX <= imaxX && iminY <= imaxY) {
            return this.VISIBLE_SOME;
        }            
        
        return this.VISIBLE_NONE;
    },
    
    /**
     * Quick and dirty computation of the on-screen distance in pixels
     * between two 2d points. We use the max of the x and y differences.
     * In case a point is null (that is, it's not on the screen), we 
     * return an arbitrarily high number.
     *
     * @private
     */
    screenDistance : function screenDistance (p0, p1) {
        if (p0 == null || p1 == null) {
            return 0;
        }
        return Math.max (Math.abs (p0.x - p1.x), Math.abs (p0.y - p1.y));
    },
    
    transformToScreen : function transformToScreen (v) {
        return this.owner.renderer.transformToScreen (v);
    },
    
    /**
     * Optionally subdivides a quad into fourn new quads, depending on the
     * position and on-screen size of the quad.
     *
     * @private
     * @param {bigshot.WebGLTexturedQuadScene} scene the scene to add quads to
     * @param {bigshot.Point3D} topLeft the top left corner of this quad
     * @param {number} width the sides of the quad, expressed in multiples of u and v
     * @param {int} divisions the current number of divisions done (increases by one for each
     * split-in-four).
     * @param {int} tx the tile column this face is in
     * @param {int} ty the tile row this face is in 
     */
    generateSubdivisionFace : function generateSubdivisionFace (scene, topLeft, width, divisions, tx, ty, transformed) {
        if (!transformed) {
            transformed = new Array (4);
            transformed[0] = this.transformToScreen (topLeft);
            var topRight = this.pt3dMultAdd (this.u, width, topLeft);
            transformed[1] = this.transformToScreen (topRight);
            
            var bottomLeft = this.pt3dMultAdd (this.v, width, topLeft);
            transformed[3] = this.transformToScreen (bottomLeft);
            
            var bottomRight = this.pt3dMultAdd (this.v, width, topRight);
            transformed[2] = this.transformToScreen (bottomRight);            
        };
        
        var numVisible = this.intersectWithView (transformed);
        
        if (numVisible == this.VISIBLE_NONE) {
            return;
        }
        
        var dmax = 0;
        for (var i = 0; i < transformed.length; ++i) {
            var next = (i + 1) % 4;
            dmax = Math.max (this.screenDistance (transformed[i], transformed[next]), dmax);
        }
        
        // Convert the distance to physical pixels
        dmax *= this.owner.browser.getDevicePixelScale ();
        
        if (divisions < this.minDivisions 
                || 
                (
                    (
                        dmax > this.owner.maxTextureMagnification * (this.tileSize - this.overlap) 
                    ) && divisions < this.maxDivisions && divisions < this.maxTesselation
                )
            ) {
                var center = this.pt3dMultAdd ({x: this.u.x + this.v.x, y: this.u.y + this.v.y, z: this.u.z + this.v.z }, width / 2, topLeft);
                var midTop = this.pt3dMultAdd (this.u, width / 2, topLeft);
                var midLeft = this.pt3dMultAdd (this.v, width / 2, topLeft);
                
                var tCenter = this.transformToScreen (center);
                var tMidLeft = this.transformToScreen (midLeft);
                var tMidTop = this.transformToScreen (midTop);
                var tMidRight = this.transformToScreen (this.pt3dMultAdd (this.u, width, midLeft));
                var tMidBottom = this.transformToScreen (this.pt3dMultAdd (this.v, width, midTop));
                
                this.generateSubdivisionFace (scene, topLeft, width / 2, divisions + 1, tx * 2, ty * 2, [transformed[0], tMidTop, tCenter, tMidLeft]);
                this.generateSubdivisionFace (scene, midTop, width / 2, divisions + 1, tx * 2 + 1, ty * 2, [tMidTop, transformed[1], tMidRight, tCenter]);
                this.generateSubdivisionFace (scene, midLeft, width / 2, divisions + 1, tx * 2, ty * 2 + 1, [tMidLeft, tCenter, tMidBottom, transformed[3]]);
                this.generateSubdivisionFace (scene, center, width / 2, divisions + 1, tx * 2 + 1, ty * 2 + 1, [tCenter, tMidRight, transformed[2], tMidBottom]);
            } else {
                this.generateFace (scene, topLeft, width, tx, ty, divisions);
            }
    },
    
    /**
     * Tests if the face has had any updated texture
     * notifications from the tile cache.
     *
     * @public
     */
    isUpdated : function () {
        return this.updated;
    },
    
    /**
     * Renders this face into a scene.
     * 
     * @public
     * @param {bigshot.WebGLTexturedQuadScene} scene the scene to render into
     */
    render : function (scene) {
        this.updated = false;
        this.viewportWidth = this.owner.renderer.getViewportWidth ();
        this.viewportHeight = this.owner.renderer.getViewportHeight ();        
        this.generateSubdivisionFace (scene, this.topLeft, this.width, 0, 0, 0);
    },
    
    /**
     * Performs post-render cleanup.
     */
    endRender : function () {
        this.tileCache.purge ();
    }
}
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * @class WebGL utility functions.
 */
bigshot.WebGLUtil = {
    /**
     * Flag indicating whether we want to wrap the WebGL context in a 
     * WebGLDebugUtils.makeDebugContext. Defaults to false.
     * 
     * @type boolean
     * @public
     */
    debug : false,
    
    /**
     * List of context identifiers WebGL may be accessed via.
     *
     * @type String[]
     * @private
     */
    contextNames : ["webgl", "experimental-webgl"],
    
    /**
     * Utility function for creating a context given a canvas and 
     * a context identifier.
     * @type WebGLRenderingContext
     * @private
     */
    createContext0 : function (canvas, context) {
        var gl = this.debug
            ?
            WebGLDebugUtils.makeDebugContext(canvas.getContext(context))
        :
        canvas.getContext (context);
        return gl;
    },
    
    /**
     * Creates a WebGL context for the given canvas, if possible.
     *
     * @public
     * @type WebGLRenderingContext
     * @param {HTMLCanvasElement} canvas the canvas
     * @return The WebGL context
     * @throws {Error} If WebGL isn't supported.
     */
    createContext : function (canvas) {
        for (var i = 0; i < this.contextNames.length; ++i) {
            try {
                var gl = this.createContext0 (canvas, this.contextNames[i]);
                if (gl) {
                    return gl;
                }
            } catch (e) {
            }
        }
        throw new Error ("Could not initialize WebGL.");
    },
    
    /**
     * Tests whether WebGL is supported.
     *
     * @type boolean
     * @public
     * @return true If WebGL is supported, false otherwise.
     */
    isWebGLSupported : function () {
        var canvas = document.createElement ("canvas");
        if (!canvas["width"]) {
            // Not even canvas support
            return false;
        }
        
        try {
            this.createContext (canvas);
            return true;
        } catch (e) {
            // No WebGL support
            return false;
        }
    }
}
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new transformation stack, initialized to the identity transform.
 *
 * @class A 3D transformation stack.
 */
bigshot.TransformStack = function () {
    /**
     * The current transform matrix.
     *
     * @type Matrix
     */
    this.mvMatrix = null;
    
    /**
     * The object-to-world transform matrix stack.
     *
     * @type Matrix[]
     */
    this.mvMatrixStack = [];
    
    this.reset ();
}

bigshot.TransformStack.prototype = {
    /**
     * Pushes the current world transform onto the stack
     * and returns a new, identical one.
     *
     * @return the new world transform matrix
     * @param {Matrix} [matrix] the new world transform. 
     * If omitted, the current is used
     * @type Matrix
     */
    push : function (matrix) {
        if (matrix) {
            this.mvMatrixStack.push (matrix.dup());
            this.mvMatrix = matrix.dup();
            return mvMatrix;
        } else {
            this.mvMatrixStack.push (this.mvMatrix.dup());
            return mvMatrix;
        }
    },
    
    /**
     * Pops the last-pushed world transform off the stack, thereby restoring it.
     *
     * @type Matrix
     * @return the previously-pushed matrix
     */
    pop : function () {
        if (this.mvMatrixStack.length == 0) {
            throw new Error ("Invalid popMatrix!");
        }
        this.mvMatrix = this.mvMatrixStack.pop();
        return mvMatrix;
    },
    
    /**
     * Resets the world transform to the identity transform.
     */
    reset : function () {
        this.mvMatrix = Matrix.I(4);
    },
    
    /**
     * Multiplies the current world transform with a matrix.
     *
     * @param {Matrix} matrix the matrix to multiply with
     */
    multiply : function (matrix) {
        this.mvMatrix = matrix.x (this.mvMatrix);
    },
    
    /**
     * Adds a translation to the world transform matrix.
     *
     * @param {bigshot.Point3D} vector the translation vector
     */
    translate : function (vector) {
        var m = Matrix.Translation($V([vector.x, vector.y, vector.z])).ensure4x4 ();
        this.multiply (m);
    },
    
    /**
     * Adds a rotation to the world transform matrix.
     *
     * @param {number} ang the angle in degrees to rotate
     * @param {bigshot.Point3D} vector the rotation vector
     */
    rotate : function (ang, vector) {
        var arad = ang * Math.PI / 180.0;
        var m = Matrix.Rotation(arad, $V([vector.x, vector.y, vector.z])).ensure4x4 ();
        this.multiply (m);
    },
    
    /**
     * Adds a rotation around the x-axis to the world transform matrix.
     *
     * @param {number} ang the angle in degrees to rotate
     */
    rotateX : function (ang) {
        this.rotate (ang, { x : 1, y : 0, z : 0 });
    },
    
    /**
     * Adds a rotation around the y-axis to the world transform matrix.
     *
     * @param {number} ang the angle in degrees to rotate
     */
    rotateY : function (ang) {
        this.rotate (ang, { x : 0, y : 1, z : 0 });
    },
    
    /**
     * Adds a rotation around the z-axis to the world transform matrix.
     *
     * @param {number} ang the angle in degrees to rotate
     */
    rotateZ : function (ang) {
        this.rotate (ang, { x : 0, y : 0, z : 1 });
    },
    
    /**
     * Multiplies the current matrix with a 
     * perspective transformation matrix.
     *
     * @param {number} fovy vertical field of view
     * @param {number} aspect viewport aspect ratio
     * @param {number} znear near image plane
     * @param {number} zfar far image plane
     */
    perspective : function (fovy, aspect, znear, zfar) {
        var m = makePerspective (fovy, aspect, znear, zfar);
        this.multiply (m);
    },
    
    matrix : function () {
        return this.mvMatrix;
    }
}
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new WebGL wrapper instance.
 *
 * @class WebGL wrapper for common {@link bigshot.VRPanorama} uses.
 * @param {HTMLCanvasElement} canvas_ the canvas
 * @see #onresize()
 */
bigshot.WebGL = function (canvas_) {
    /**
     * The html canvas element we'll be rendering in.
     *
     * @type HTMLCanvasElement
     */
    this.canvas = canvas_;
    
    /**
     * Our WebGL context.
     *
     * @type WebGLRenderingContext
     */
    this.gl = bigshot.WebGLUtil.createContext (this.canvas); 
            
    /**
     * The current object-to-world transform matrix.
     *
     * @type bigshot.TransformStack
     */
    this.mvMatrix = new bigshot.TransformStack ();
    
    /**
     * The current perspective transform matrix.
     *
     * @type bigshot.TransformStack
     */
    this.pMatrix = new bigshot.TransformStack ();
    
    /**
     * The current shader program.
     */
    this.shaderProgram = null;
    
    this.onresize ();
}

bigshot.WebGL.prototype = {
    /**
     * Must be called when the canvas element is resized.
     *
     * @public
     */
    onresize : function () {
        this.gl.viewportWidth = this.canvas.width;
        this.gl.viewportHeight = this.canvas.height;
    },
    
    /**
     * Fragment shader. Taken from the "Learning WebGL" lessons:
     *     http://learningwebgl.com/blog/?p=571
     */
    fragmentShader : 
        "#ifdef GL_ES\n" + 
        "    precision highp float;\n" + 
        "#endif\n" + 
        "\n" + 
        "varying vec2 vTextureCoord;\n" + 
        "\n" + 
        "uniform sampler2D uSampler;\n" + 
        "\n" + 
        "void main(void) {\n" + 
        "    gl_FragColor = texture2D(uSampler, vec2(vTextureCoord.s, vTextureCoord.t));\n" + 
        "}\n",
    
    /**
     * Vertex shader. Taken from the "Learning WebGL" lessons:
     *     http://learningwebgl.com/blog/?p=571
     */
    vertexShader : 
        "attribute vec3 aVertexPosition;\n" +
        "attribute vec2 aTextureCoord;\n" +
        "\n" +
        "uniform mat4 uMVMatrix;\n" +
        "uniform mat4 uPMatrix;\n" +
        "\n" +
        "varying vec2 vTextureCoord;\n" +
        "\n" +
        "void main(void) {\n" +
        "    gl_Position = uPMatrix * uMVMatrix * vec4(aVertexPosition, 1.0);\n" +
        "    vTextureCoord = aTextureCoord;\n" +
        "}",
    
    /**
     * Creates a new shader.
     *
     * @type WebGLShader
     * @param {String} source the source code
     * @param {int} type the shader type, one of WebGLRenderingContext.FRAGMENT_SHADER or 
     * WebGLRenderingContext.VERTEX_SHADER
     */
    createShader : function (source, type) {
        var shader = this.gl.createShader (type);
        this.gl.shaderSource (shader, source);
        this.gl.compileShader (shader);
        
        if (!this.gl.getShaderParameter (shader, this.gl.COMPILE_STATUS)) {
            alert (this.gl.getShaderInfoLog (shader));
            return null;
        }
        
        return shader;
    },
    
    /**
     * Creates a new fragment shader.
     *
     * @type WebGLShader
     * @param {String} source the source code
     */
    createFragmentShader : function (source) {
        return this.createShader (source, this.gl.FRAGMENT_SHADER);
    },
    
    /**
     * Creates a new vertex shader.
     *
     * @type WebGLShader
     * @param {String} source the source code
     */
    createVertexShader : function (source) {
        return this.createShader (source, this.gl.VERTEX_SHADER);
    },
    
    /**
     * Initializes the shaders.
     */
    initShaders : function () {
        this.shaderProgram = this.gl.createProgram ();
        this.gl.attachShader (this.shaderProgram, this.createVertexShader (this.vertexShader));
        this.gl.attachShader (this.shaderProgram, this.createFragmentShader (this.fragmentShader));
        this.gl.linkProgram (this.shaderProgram);
        
        if (!this.gl.getProgramParameter (this.shaderProgram, this.gl.LINK_STATUS)) {
            throw new Error ("Could not initialise shaders");
            return;
        }
        
        this.gl.useProgram (this.shaderProgram);
        
        this.shaderProgram.vertexPositionAttribute = this.gl.getAttribLocation (this.shaderProgram, "aVertexPosition");
        this.gl.enableVertexAttribArray (this.shaderProgram.vertexPositionAttribute);
        
        this.shaderProgram.textureCoordAttribute = this.gl.getAttribLocation (this.shaderProgram, "aTextureCoord");
        this.gl.enableVertexAttribArray (this.shaderProgram.textureCoordAttribute);
        
        this.shaderProgram.pMatrixUniform = this.gl.getUniformLocation(this.shaderProgram, "uPMatrix");
        this.shaderProgram.mvMatrixUniform = this.gl.getUniformLocation(this.shaderProgram, "uMVMatrix");
        this.shaderProgram.samplerUniform = this.gl.getUniformLocation(this.shaderProgram, "uSampler");
    },

    
    /**
     * Sets the matrix parameters ("uniforms", since the variables are declared as uniform) in the shaders.
     */
    setMatrixUniforms : function () {
        this.gl.uniformMatrix4fv (this.shaderProgram.pMatrixUniform, false, new Float32Array(this.pMatrix.matrix().flatten()));
        this.gl.uniformMatrix4fv (this.shaderProgram.mvMatrixUniform, false, new Float32Array(this.mvMatrix.matrix().flatten()));
    },
    
    /**
     * Creates a texture from an image.
     *
     * @param {HTMLImageElement or HTMLCanvasElement} image the image
     * @type WebGLTexture
     * @return An initialized texture
     */
    createImageTextureFromImage : function (image, minFilter, magFilter) {
        var texture = this.gl.createTexture();
        this.handleImageTextureLoaded (this, texture, image, minFilter, magFilter);
        return texture;
    },
    
    /**
     * Creates a texture from a source url.
     *
     * @param {String} source the URL of the image
     * @return WebGLTexture
     */
    createImageTextureFromSource : function (source, minFilter, magFilter) {
        var image = new Image();
        var texture = this.gl.createTexture();
        
        var that = this;
        image.onload = function () {
            that.handleImageTextureLoaded (that, texture, image, minFilter, magFilter);
        }
        
        image.src = source;
        
        return texture;
    },
    
    /**
     * Uploads the image data to the texture memory. Called when the texture image
     * has finished loading.
     *
     * @private
     */
    handleImageTextureLoaded : function (that, texture, image, minFilter, magFilter) {
        that.gl.bindTexture (that.gl.TEXTURE_2D, texture);        
        that.gl.texImage2D (that.gl.TEXTURE_2D, 0, that.gl.RGBA, that.gl.RGBA, that.gl.UNSIGNED_BYTE, image);
        that.gl.texParameteri (that.gl.TEXTURE_2D, that.gl.TEXTURE_MAG_FILTER, magFilter ? magFilter : that.gl.NEAREST);
        that.gl.texParameteri (that.gl.TEXTURE_2D, that.gl.TEXTURE_MIN_FILTER, minFilter ? minFilter : that.gl.NEAREST);
        that.gl.texParameteri (that.gl.TEXTURE_2D, that.gl.TEXTURE_WRAP_S, that.gl.CLAMP_TO_EDGE);
        that.gl.texParameteri (that.gl.TEXTURE_2D, that.gl.TEXTURE_WRAP_T, that.gl.CLAMP_TO_EDGE);
        if (minFilter == that.gl.NEAREST_MIPMAP_NEAREST
                || minFilter == that.gl.LINEAR_MIPMAP_NEAREST
                    || minFilter == that.gl.NEAREST_MIPMAP_LINEAR
                    || minFilter == that.gl.LINEAR_MIPMAP_LINEAR) {
                        that.gl.generateMipmap(that.gl.TEXTURE_2D);
                    }
        
        that.gl.bindTexture (that.gl.TEXTURE_2D, null);
    },
    
    deleteTexture : function (texture) {
        this.gl.deleteTexture (texture);
    },
    
    dispose : function () {
        delete this.canvas;
        delete this.gl;
    }
};
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * @class Abstract base for 3d rendering system.
 */
bigshot.VRRenderer = function () {
}

bigshot.VRRenderer.prototype = {
    /**
     * Creates a new {@link bigshot.VRTileCache}, appropriate for the rendering system.
     *
     * @param {function()} onloaded function that is called whenever a texture tile has been loaded
     * @param {function()} onCacheInit function that is called when the texture cache is fully initialized
     * @param {bigshot.VRPanoramaParameters} parameters the parameters for the panorama
     */
    createTileCache : function (onloaded, onCacheInit, parameters) {},
    
    /**
     * Creates a bigshot.TexturedQuadScene.
     */
    createTexturedQuadScene : function () {},
    
    /**
     * Creates a bigshot.TexturedQuad.
     *
     * @param {bigshot.Point3D} p the top-left corner of the quad
     * @param {bigshot.Point3D} u a vector going along the top edge of the quad
     * @param {bigshot.Point3D} v a vector going down the left edge of the quad
     * @param {Object} texture a texture to use for the quad. The texture type may vary among different
     * VRRenderer implementations. The VRTileCache that is created using the createTileCache method will
     * supply the correct type.
     */
    createTexturedQuad : function (p, u, v, texture) {},
    
    /**
     * Returns the viewport width, in pixels.
     *
     * @type int
     */
    getViewportWidth : function () {},
    
    /**
     * Returns the viewport height, in pixels.
     *
     * @type int
     */
    getViewportHeight : function () {},
    
    /**
     * Transforms a vector to world coordinates.
     *
     * @param {bigshot.Point3D} v the view-space point to transform
     */
    transformToWorld : function (v) {},
    
    /**
     * Transforms a world vector to screen coordinates.
     *
     * @param {bigshot.Point3D} worldVector the world-space point to transform
     */
    transformWorldToScreen : function (worldVector) {},
    
    /**
     * Transforms a 3D vector to screen coordinates.
     *
     * @param {bigshot.Point3D} vector the vector to transform. 
     * If it is already in homogenous coordinates (4-element array) 
     * the transformation is faster. Otherwise it will be converted.
     */
    transformToScreen : function (vector) {},
    
    /**
     * Disposes the renderer and associated resources.
     */
    dispose : function () {},
    
    /**
     * Called to begin a render.
     *
     * @param {bigshot.Rotation} rotation the rotation of the viewer
     * @param {number} fov the vertical field of view, in degrees
     * @param {bigshot.Point3D} translation the position of the viewer in world space
     * @param {bigshot.Rotation} rotationOffsets the rotation to apply to the VR cube 
     * before the viewer rotation is applied
     */
    beginRender : function (rotation, fov, translation, rotationOffsets) {},
    
    /**
     * Called to end a render.
     */
    endRender : function () {},
    
    /**
     * Called by client code to notify the renderer that the viewport has been resized.
     */
    onresize : function () {},
    
    /**
     * Resizes the viewport.
     *
     * @param {int} w the new width of the viewport, in pixels
     * @param {int} h the new height of the viewport, in pixels
     */
    resize : function (w, h) {},
    
    /**
     * Gets the container element for the renderer. This is used
     * when calling the requestAnimationFrame API.
     */
    getElement : function () {}
}
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */
    
/**
 * @class Abstract VR renderer base class.
 */
bigshot.AbstractVRRenderer = function () {
}

bigshot.AbstractVRRenderer.prototype = {
    /**
     * Transforms a vector to world coordinates.
     *
     * @param {bigshot.Point3D} vector the vector to transform
     */
    transformToWorld : function transformToWorld (vector) {
        var world = this.mvMatrix.matrix ().xPoint3Dhom1 (vector);
        
        return world;
    },
    
    /**
     * Transforms a world vector to screen coordinates.
     *
     * @param {bigshot.Point3D} world the world-vector to transform
     */
    transformWorldToScreen : function transformWorldToScreen (world) {
        if (world.z > 0) {
            return null;
        }
        
        var screen = this.pMatrix.matrix ().xPoint3Dhom (world);
        if (Math.abs (screen.w) < Sylvester.precision) {
            return null;
        }
        
        var sx = screen.x;
        var sy = screen.y;
        var sz = screen.z;
        var vw = this.getViewportWidth ();
        var vh = this.getViewportHeight ();
        
        var r = {
            x: (vw / 2) * sx / sz + vw / 2, 
            y: - (vh / 2) * sy / sz + vh / 2
        };
        return r;
    },
    
    /**
     * Transforms a vector to screen coordinates.
     *
     * @param {bigshot.Point3D} vector the vector to transform
     * @return the transformed vector, or null if the vector is nearer than the near-z plane.
     */
    transformToScreen : function transformToScreen (vector) {
        var sel = this.mvpMatrix.xPoint3Dhom (vector);
        
        if (sel.z < 0) {
            return null;
        }
        
        var sz = sel.w;
        
        if (Math.abs (sel.w) < Sylvester.precision) {
            return null;
        }
        
        var sx = sel.x;
        var sy = sel.y;
        var vw = this.getViewportWidth ();
        var vh = this.getViewportHeight ();
        
        var r = {
            x: (vw / 2) * sx / sz + vw / 2, 
            y: - (vh / 2) * sy / sz + vh / 2
        };

        return r;
    }
}
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * @class CSS 3D Transform-based renderer.
 * @param {HTMLElement} _container the HTML container element for the render viewport
 *
 * @augments bigshot.VRRenderer
 */
bigshot.CSS3DVRRenderer = function (_container) {
    this.container = _container;
    this.canvasOrigin = document.createElement ("div");
    
    this.canvasOrigin.style.WebkitTransformOrigin = "0px 0px 0px";
    this.canvasOrigin.style.WebkitTransformStyle = "preserve-3d";
    this.canvasOrigin.style.WebkitPerspective= "600px";
    
    this.canvasOrigin.style.position = "relative";
    this.canvasOrigin.style.left = "50%";
    this.canvasOrigin.style.top = "50%";
    
    this.container.appendChild (this.canvasOrigin);
    
    this.viewport = document.createElement ("div");
    this.viewport.style.WebkitTransformOrigin = "0px 0px 0px";
    this.viewport.style.WebkitTransformStyle = "preserve-3d";
    this.canvasOrigin.appendChild (this.viewport);
    
    this.world = document.createElement ("div");
    this.world.style.WebkitTransformOrigin = "0px 0px 0px";
    this.world.style.WebkitTransformStyle = "preserve-3d";
    this.viewport.appendChild (this.world);
    
    this.browser.removeAllChildren (this.world);
    
    this.view = null;
    
    this.mvMatrix = new bigshot.TransformStack ();
    
    this.yaw = 0;
    this.pitch = 0;
    this.fov = 0;
    this.pMatrix = new bigshot.TransformStack ();
    
    this.onresize = function () {
    };
    
    this.viewportSize = null;
};

bigshot.CSS3DVRRenderer.prototype = {
    browser : new bigshot.Browser (),
    
    dispose : function () {
        
    },
    
    createTileCache : function (onloaded, onCacheInit, parameters) {
        return new bigshot.ImageVRTileCache (onloaded, onCacheInit, parameters);
    },
    
    createTexturedQuadScene : function () {
        return new bigshot.CSS3DTexturedQuadScene (this.world, 128, this.view);
    },
    
    createTexturedQuad : function (p, u, v, texture) {
        return new bigshot.CSS3DTexturedQuad (p, u, v, texture);
    },
    
    getElement : function () {
        return this.container;
    },
    
    supportsUpdate : function () {
        return false;
    },
    
    getViewportWidth : function () {
        if (this.viewportSize) {
            return this.viewportSize.w;
        }
        return this.browser.getElementSize (this.container).w;
    },
    
    getViewportHeight : function () {
        if (this.viewportSize) {
            return this.viewportSize.h;
        }
        return this.browser.getElementSize (this.container).h;
    },
    
    onresize : function () {
    },
    
    resize : function (w, h) {
        if (this.container.style.width != "") {
            this.container.style.width = w + "px";
        }
        if (this.container.style.height != "") {
            this.container.style.height = h + "px";
        }
    },
    
    beginRender : function (rotation, fov, translation, rotationOffsets) {
        this.viewportSize = this.browser.getElementSize (this.container);
        
        this.yaw = rotation.y;
        this.pitch = rotation.p;
        this.fov = fov;
        
        var halfFovInRad = 0.5 * fov * Math.PI / 180;
        var halfHeight = this.getViewportHeight () / 2;
        var perspectiveDistance = halfHeight / Math.tan (halfFovInRad);
        
        this.mvMatrix.reset ();
        
        this.view = translation;
        this.mvMatrix.translate (this.view);
        
        
        this.mvMatrix.rotateZ (rotationOffsets.r);
        this.mvMatrix.rotateX (rotationOffsets.p);
        this.mvMatrix.rotateY (rotationOffsets.y);
        
        this.mvMatrix.rotateY (this.yaw);
        this.mvMatrix.rotateX (this.pitch);
        
        
        this.pMatrix.reset ();
        this.pMatrix.perspective (this.fov, this.getViewportWidth () / this.getViewportHeight (), 0.1, 100.0);
        
        this.mvpMatrix = this.pMatrix.matrix ().multiply (this.mvMatrix.matrix ());
        
        this.canvasOrigin.style.WebkitPerspective= perspectiveDistance + "px";
        
        for (var i = this.world.children.length - 1; i >= 0; --i) {
            this.world.children[i].inWorld = 1;
        }
        
        this.world.style.WebkitTransform = 
            "rotate3d(1,0,0," + (-rotation.p) + "deg) " +
            "rotate3d(0,1,0," + rotation.y + "deg) " +
            "rotate3d(0,1,0," + (rotationOffsets.y) + "deg) " +
            "rotate3d(1,0,0," + (-rotationOffsets.p) + "deg) " +
            "rotate3d(0,0,1," + (-rotationOffsets.r) + "deg) ";
        this.world.style.WebkitTransformStyle = "preserve-3d";
        this.world.style.WebKitBackfaceVisibility = "hidden";
        
        this.viewport.style.WebkitTransform = 
            "translateZ(" + perspectiveDistance + "px)";
    },
    
    endRender : function () {
        for (var i = this.world.children.length - 1; i >= 0; --i) {
            var child = this.world.children[i];
            if (!child.inWorld || child.inWorld != 2) {
                delete child.inWorld;
                this.world.removeChild (child);
            }
        }
        
        this.viewportSize = null;
    }    
};

bigshot.Object.extend (bigshot.CSS3DVRRenderer, bigshot.AbstractVRRenderer);
bigshot.Object.validate ("bigshot.CSS3DVRRenderer", bigshot.VRRenderer);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a textured quad object.
 *
 * @class An abstraction for textured quads. Used in the
 * {@link bigshot.CSS3DTexturedQuadScene}.
 *
 * @param {bigshot.Point3D} p the top-left corner of the quad
 * @param {bigshot.Point3D} u vector pointing from p along the top edge of the quad
 * @param {bigshot.Point3D} v vector pointing from p along the left edge of the quad
 * @param {HTMLImageElement} the image to use.
 */
bigshot.CSS3DTexturedQuad = function (p, u, v, image) {
    this.p = p;
    this.u = u;
    this.v = v;
    this.image = image;
}

bigshot.CSS3DTexturedQuad.prototype = {
    /**
     * Computes the cross product of two vectors.
     * 
     * @param {bigshot.Point3D} a the first vector
     * @param {bigshot.Point3D} b the second vector
     * @type bigshot.Point3D
     * @return the cross product
     */
    crossProduct : function crossProduct (a, b) {
        return {
            x : a.y*b.z-a.z*b.y, 
            y : a.z*b.x-a.x*b.z, 
            z : a.x*b.y-a.y*b.x
        };
    },
    
    /**
     * Stringifies a vector as the x, y, and z components 
     * separated by commas.
     * 
     * @param {bigshot.Point3D} u the vector
     * @type String
     * @return the stringified vector
     */
    vecToStr : function vecToStr (u) {
        return (u.x) + "," + (u.y) + "," + (u.z);
    },
    
    /**
     * Creates a CSS3D matrix3d transform from 
     * an origin point and two basis vectors
     * 
     * @param {bigshot.Point3D} tl the top left corner
     * @param {bigshot.Point3D} u the vector pointing along the top edge
     * @param {bigshot.Point3D} y the vector pointing down the left edge
     * @type String
     * @return the matrix3d statement
     */
    quadTransform : function quadTransform (tl, u, v) {
        var w = this.crossProduct (u, v);
        var res = 
            "matrix3d(" + 
            this.vecToStr (u) + ",0," + 
        this.vecToStr (v) + ",0," + 
        this.vecToStr (w) + ",0," + 
        this.vecToStr (tl) + ",1)";
        return res;
    },
    
    /**
     * Computes the norm of a vector.
     *
     * @param {bigshot.Point3D} vec the vector
     */
    norm : function norm (vec) {
        return Math.sqrt (vec.x * vec.x + vec.y * vec.y + vec.z * vec.z);
    },
    
    /**
     * Renders the quad.
     *
     * @param {HTMLElement} world the world element
     * @param {number} scale the scale factor to apply to world space to get CSS pixel distances
     * @param {bigshot.Point3D} view the viewer position in world space
     */
    render : function render (world, scale, view) {
        var s = scale / (this.image.width - 1);
        var ps = scale * 1.0;
        var p = this.p;
        var u = this.u;
        var v = this.v;
        
        this.image.style.position = "absolute";
        if (!this.image.inWorld || this.image.inWorld != 1) {
            world.appendChild (this.image);
        }
        this.image.inWorld = 2;
        this.image.style.WebkitTransformOrigin = "0px 0px 0px";
        this.image.style.WebkitTransform = 
            this.quadTransform ({
                    x : (p.x + view.x) * ps, 
                    y : (-p.y + view.y) * ps, 
                    z : (p.z + view.z) * ps
                }, {
                    x : u.x * s, 
                    y : -u.y * s, 
                    z : u.z * s
                }, {
                    x : v.x * s, 
                    y : -v.y * s, 
                    z : v.z * s
                });
    }
}
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a textured quad scene.
 *
 * @param {HTMLElement} world element used as container for 
 * the world coordinate system.
 * @param {number} scale the scaling factor to use to avoid 
 * numeric errors.
 * @param {bigshot.Point3D} view the 3d-coordinates of the viewer
 *
 * @class A scene consisting of a number of quads, all with
 * a unique texture. Used by the {@link bigshot.VRPanorama} to render the VR cube.
 *
 * @see bigshot.CSS3DTexturedQuad
 */
bigshot.CSS3DTexturedQuadScene = function (world, scale, view) {
    this.quads = new Array ();
    this.world = world;
    this.scale = scale;
    this.view = view;
}

bigshot.CSS3DTexturedQuadScene.prototype = {  
    /** 
     * Adds a new quad to the scene.
     *
     * @param {bigshot.TexturedQuad} quad the quad to add to the scene
     */
    addQuad : function (quad) {
        this.quads.push (quad);
    },
    
    /** 
     * Renders all quads.
     */
    render : function () {            
        for (var i = 0; i < this.quads.length; ++i) {
            this.quads[i].render (this.world, this.scale, this.view);
        }
    }
};
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * @class Abstract base for textured quad scenes.
 */
bigshot.TexturedQuadScene = function () {
}

bigshot.TexturedQuadScene.prototype = {
    /**
     * Adds a quad to the scene.
     */
    addQuad : function (quad) {},
    
    /**
     * Renders the scene.
     */
    render : function () {}
};
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * @class WebGL renderer.
 */
bigshot.WebGLVRRenderer = function (container) {
    this.container = container;
    
    this.canvas = document.createElement ("canvas");
    this.canvas.width = 480;
    this.canvas.height = 480;
    this.canvas.style.position = "absolute";
    this.container.appendChild (this.canvas);
    
    this.webGl = new bigshot.WebGL (this.canvas);
    this.webGl.initShaders ();
    this.webGl.gl.clearColor(0.0, 0.0, 0.0, 1.0);
    this.webGl.gl.blendFunc (this.webGl.gl.ONE, this.webGl.gl.ZERO);
    this.webGl.gl.enable (this.webGl.gl.BLEND);
    this.webGl.gl.disable (this.webGl.gl.DEPTH_TEST);
    this.webGl.gl.clearDepth (1.0);
    
    var that = this;
    this.buffers = new bigshot.TimedWeakReference (function () {
            return that.setupBuffers ();
        }, function (heldObject) {
            that.disposeBuffers (heldObject);
        }, 1000);
}

bigshot.WebGLVRRenderer.prototype = {
    createTileCache : function (onloaded, onCacheInit, parameters) {
        return new bigshot.TextureTileCache (onloaded, onCacheInit, parameters, this.webGl);
    },
    
    createTexturedQuadScene : function () {
        return new bigshot.WebGLTexturedQuadScene (this.webGl, this.buffers);
    },
    
    setupBuffers : function () {
        var vertexPositionBuffer = this.webGl.gl.createBuffer();
        
        var textureCoordBuffer = this.webGl.gl.createBuffer();
        this.webGl.gl.bindBuffer(this.webGl.gl.ARRAY_BUFFER, textureCoordBuffer);
        var textureCoords = [
            // Front face
            0.0,  0.0,
            1.0,  0.0,
            1.0,  1.0,
            0.0,  1.0
        ];
        this.webGl.gl.bufferData (this.webGl.gl.ARRAY_BUFFER, new Float32Array (textureCoords), this.webGl.gl.STATIC_DRAW);
        
        var vertexIndexBuffer = this.webGl.gl.createBuffer();
        this.webGl.gl.bindBuffer(this.webGl.gl.ELEMENT_ARRAY_BUFFER, vertexIndexBuffer);            
        var vertexIndexes = [
            0, 2, 1,
            0, 3, 2
        ];
        this.webGl.gl.bufferData(this.webGl.gl.ELEMENT_ARRAY_BUFFER, new Uint16Array (vertexIndexes), this.webGl.gl.STATIC_DRAW);
        
        this.webGl.gl.bindBuffer(this.webGl.gl.ARRAY_BUFFER, textureCoordBuffer);
        this.webGl.gl.vertexAttribPointer(this.webGl.shaderProgram.textureCoordAttribute, 2, this.webGl.gl.FLOAT, false, 0, 0);
        
        this.webGl.gl.bindBuffer(this.webGl.gl.ARRAY_BUFFER, vertexPositionBuffer);
        this.webGl.gl.vertexAttribPointer(this.webGl.shaderProgram.vertexPositionAttribute, 3, this.webGl.gl.FLOAT, false, 0, 0);
        
        return {
            vertexPositionBuffer : vertexPositionBuffer,
            textureCoordBuffer : textureCoordBuffer,
            vertexIndexBuffer : vertexIndexBuffer
        };
    },
    
    dispose : function () {
        this.buffers.dispose ();
        this.container.removeChild (this.canvas);
        delete this.canvas;
        this.webGl.dispose ();
        delete this.webGl;
    },
    
    disposeBuffers : function (buffers) {
        this.webGl.gl.deleteBuffer (buffers.vertexPositionBuffer);
        this.webGl.gl.deleteBuffer (buffers.vertexIndexBuffer);
        this.webGl.gl.deleteBuffer (buffers.textureCoordBuffer);
    },
    
    getElement : function () {
        return this.canvas;
    },
    
    supportsUpdate : function () {
        return false;
    },
    
    createTexturedQuad : function (p, u, v, texture) {
        return new bigshot.WebGLTexturedQuad (p, u, v, texture);
    },
    
    getViewportWidth : function () {
        return this.webGl.gl.viewportWidth;
    },
    
    getViewportHeight : function () {
        return this.webGl.gl.viewportHeight;
    },
    
    beginRender : function (rotation, fov, translation, rotationOffsets) {
        this.webGl.gl.viewport (0, 0, this.webGl.gl.viewportWidth, this.webGl.gl.viewportHeight);
        
        this.webGl.pMatrix.reset ();
        this.webGl.pMatrix.perspective (fov, this.webGl.gl.viewportWidth / this.webGl.gl.viewportHeight, 0.1, 100.0);
        
        this.webGl.mvMatrix.reset ();
        this.webGl.mvMatrix.translate (translation);
        this.webGl.mvMatrix.rotateZ (rotationOffsets.r);
        this.webGl.mvMatrix.rotateX (rotationOffsets.p);
        this.webGl.mvMatrix.rotateY (rotationOffsets.y);
        this.webGl.mvMatrix.rotateY (rotation.y);
        this.webGl.mvMatrix.rotateX (rotation.p);
        
        this.mvMatrix = this.webGl.mvMatrix;
        this.pMatrix = this.webGl.pMatrix;
        this.mvpMatrix = this.pMatrix.matrix ().multiply (this.mvMatrix.matrix ());
    },
    
    endRender : function () {
        
    },
    
    resize : function (w, h) {
        this.canvas.width = w;
        this.canvas.height = h;
        if (this.container.style.width != "") {
            this.container.style.width = w + "px";
        }
        if (this.container.style.height != "") {
            this.container.style.height = h + "px";
        }
    },
    
    onresize : function () {
        this.webGl.onresize ();
    }
}

bigshot.Object.extend (bigshot.WebGLVRRenderer, bigshot.AbstractVRRenderer);
bigshot.Object.validate ("bigshot.WebGLVRRenderer", bigshot.VRRenderer);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * @class Abstract base for textured quads.
 */
bigshot.TexturedQuad = function () {
};
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a textured quad object.
 *
 * @class An abstraction for textured quads. Used in the
 * {@link bigshot.WebGLTexturedQuadScene}.
 *
 * @param {bigshot.Point3D} p the top-left corner of the quad
 * @param {bigshot.Point3D} u vector pointing from p along the top edge of the quad
 * @param {bigshot.Point3D} v vector pointing from p along the left edge of the quad
 * @param {WebGLTexture} the texture to use.
 */
bigshot.WebGLTexturedQuad = function (p, u, v, texture) {
    this.p = p;
    this.u = u;
    this.v = v;
    this.texture = texture;
}

bigshot.WebGLTexturedQuad.prototype = {
    
    /**
     * Renders the quad using the given {@link bigshot.WebGL} instance.
     * Currently creates, fills, draws with and then deletes three buffers -
     * not very efficient, but works.
     *
     * @param {bigshot.WebGL} webGl the WebGL wrapper instance to use for rendering.
     */
    render : function (webGl, vertexPositionBuffer, textureCoordBuffer, vertexIndexBuffer) {
        webGl.gl.bindBuffer(webGl.gl.ARRAY_BUFFER, vertexPositionBuffer);
        var vertices = [
            this.p.x, this.p.y,  this.p.z,
            this.p.x + this.u.x, this.p.y + this.u.y,  this.p.z + this.u.z,
            this.p.x + this.u.x + this.v.x, this.p.y + this.u.y + this.v.y,  this.p.z + this.u.z + this.v.z,
            this.p.x + this.v.x, this.p.y + this.v.y,  this.p.z + this.v.z
        ];
        webGl.gl.bufferData(webGl.gl.ARRAY_BUFFER, new Float32Array (vertices), webGl.gl.STATIC_DRAW);
        
        webGl.gl.activeTexture(webGl.gl.TEXTURE0);
        webGl.gl.bindTexture(webGl.gl.TEXTURE_2D, this.texture);
        webGl.gl.uniform1i(webGl.shaderProgram.samplerUniform, 0);
        
        webGl.gl.bindBuffer(webGl.gl.ELEMENT_ARRAY_BUFFER, vertexIndexBuffer);
        webGl.gl.drawElements(webGl.gl.TRIANGLES, 6, webGl.gl.UNSIGNED_SHORT, 0);
        
        webGl.gl.bindTexture(webGl.gl.TEXTURE_2D, null);
    }
}
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a textured quad scene.
 *
 * @param {bigshot.WebGL} webGl the webGl instance to use for rendering.
 *
 * @class A "scene" consisting of a number of quads, all with
 * a unique texture. Used by the {@link bigshot.VRPanorama} to render the VR cube.
 *
 * @see bigshot.WebGLTexturedQuad
 */
bigshot.WebGLTexturedQuadScene = function (webGl, buffers) {
    this.quads = new Array ();
    this.webGl = webGl;
    this.buffers = buffers;
}

bigshot.WebGLTexturedQuadScene.prototype = {
    /** 
     * Adds a new quad to the scene.
     */
    addQuad : function (quad) {
        this.quads.push (quad);
    },
    
    /** 
     * Renders all quads.
     */
    render : function () {
        var b = this.buffers.get ();
        var vertexPositionBuffer = b.vertexPositionBuffer;
        var textureCoordBuffer = b.textureCoordBuffer;
        var vertexIndexBuffer = b.vertexIndexBuffer;
        
        this.webGl.setMatrixUniforms();
        
        for (var i = 0; i < this.quads.length; ++i) {
            this.quads[i].render (this.webGl, vertexPositionBuffer, textureCoordBuffer, vertexIndexBuffer);
        }
    }
};
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new VR panorama parameter object and populates it with default values for
 * all values not explicitly given.
 *
 * @class VRPanoramaParameters parameter object.
 * You need not set any fields that can be read from the image descriptor that 
 * MakeImagePyramid creates. See the {@link bigshot.VRPanorama}
 * documentation for required parameters.
 *
 * <p>Usage:
 *
 * @example
 * var bvr = new bigshot.VRPanorama (
 *     new bigshot.VRPanoramaParameters ({
 *         basePath : "/bigshot.php?file=myvr.bigshot",
 *         fileSystemType : "archive",
 *         container : document.getElementById ("bigshot_canvas")
 *         }));
 * @param values named parameter map, see the fields below for parameter names and types.
 * @see bigshot.VRPanorama
 */
bigshot.VRPanoramaParameters = function (values) {
    /**
     * Size of low resolution preview image along the longest image
     * dimension. The preview is assumed to have the same aspect
     * ratio as the full image (specified by width and height).
     *
     * @default <i>Optional</i> set by MakeImagePyramid and loaded from descriptor
     * @type int
     * @public
     */
    this.posterSize = 0;
    
    /**
     * Url for the image tile to show while the tile is loading and no 
     * low-resolution preview is available.
     *
     * @default <code>null</code>, which results in an all-black image
     * @type String
     * @public
     */
    this.emptyImage = null;
    
    /**
     * Suffix to append to the tile filenames. Typically <code>".jpg"</code> or 
     * <code>".png"</code>.
     *
     * @default <i>Optional</i> set by MakeImagePyramid and loaded from descriptor
     * @type String
     */
    this.suffix = null;
    
    /**
     * The width of the full image; in pixels.
     *
     * @default <i>Optional</i> set by MakeImagePyramid and loaded from descriptor
     * @type int
     */
    this.width = 0;
    
    /**
     * The height of the full image; in pixels.
     *
     * @default <i>Optional</i> set by MakeImagePyramid and loaded from descriptor
     * @type int
     */
    this.height = 0;
    
    /**
     * For {@link bigshot.VRPanorama}, the {@code div} to render into.
     *
     * @type HTMLDivElement
     */
    this.container = null;
    
    /**
     * The maximum number of times to split a cube face into four quads.
     *
     * @type int
     * @default <i>Optional</i> set by MakeImagePyramid and loaded from descriptor
     */
    this.maxTesselation = -1;
    
    /**
     * Size of one tile in pixels.
     *
     * @type int
     * @default <i>Optional</i> set by MakeImagePyramid and loaded from descriptor
     */
    this.tileSize = 0;
    
    /**
     * Tile overlap. Not implemented.
     *
     * @type int
     * @default <i>Optional</i> set by MakeImagePyramid and loaded from descriptor
     */
    this.overlap = 0;
    
    /**
     * Base path for the image. This is filesystem dependent; but for the two most common cases
     * the following should be set
     *
     * <ul>
     * <li><b>archive</b>= The basePath is <code>"&lt;path&gt;/bigshot.php?file=&lt;path-to-bigshot-archive-relative-to-bigshot.php&gt;"</code>;
     *     for example; <code>"/bigshot.php?file=images/bigshot-sample.bigshot"</code>.
     * <li><b>folder</b>= The basePath is <code>"&lt;path-to-image-folder&gt;"</code>;
     *     for example; <code>"/images/bigshot-sample"</code>.
     * </ul>
     *
     * @type String
     */
    this.basePath = null;
    
    /**
     * The file system type. Used to create a filesystem instance unless
     * the fileSystem field is set. Possible values are <code>"archive"</code>, 
     * <code>"folder"</code> or <code>"dzi"</code>.
     *
     * @type String
     * @default "folder"
     */
    this.fileSystemType = "folder";
    
    /**
     * A reference to a filesystem implementation. If set; it overrides the
     * fileSystemType field.
     *
     * @default set depending on value of bigshot.VRPanoramaParameters#fileSystemType
     * @type bigshot.FileSystem
     */
    this.fileSystem = null;
    
    /**
     * Object used to load data files.
     *
     * @default bigshot.DefaultDataLoader
     * @type bigshot.DataLoader
     */
    this.dataLoader = new bigshot.DefaultDataLoader ();
    
    /**
     * The maximum magnification for the texture tiles making up the VR cube.
     * Used for level-of-detail tesselation.
     * A value of 1.0 means that textures will never be stretched (one texture pixel will
     * always be at most one screen pixel), unless there is no more detailed texture available. 
     * A value of 2.0 means that textures may be stretched at most 2x (one texture pixel 
     * will always be at most 2x2 screen pixels)
     * The bigger the value, the less texture data is required, but quality suffers.
     *
     * @type number
     * @default 1.0
     */
    this.maxTextureMagnification = 1.0;
    
    /**
     * The WebGL texture filter to use for magnifying textures. 
     * Possible values are all values valid for <code>TEXTURE_MAG_FILTER</code>.
     * <code>null</code> means <code>NEAREST</code>. 
     *
     * @default null / NEAREST.
     */
    this.textureMagFilter = null;
    
    /**
     * The WebGL texture filter to use for supersampling (minifying) textures. 
     * Possible values are all values valid for <code>TEXTURE_MIN_FILTER</code>.
     * <code>null</code> means <code>NEAREST</code>. 
     *
     * @default null / NEAREST.
     */
    this.textureMinFilter = null;
    
    /**
     * Minimum vertical field of view in degrees.
     *
     * @default 2.0
     * @type number
     */
    this.minFov = 2.0;
    
    /**
     * Maximum vertical field of view in degrees.
     *
     * @default 90.0
     * @type number
     */
    this.maxFov = 90;
    
    /**
     * Minimum pitch in degrees.
     *
     * @default -90
     * @type number
     */
    this.minPitch = -90;
    
    /**
     * Maximum pitch in degrees.
     *
     * @default 90.0
     * @type number
     */
    this.maxPitch = 90;
    
    /**
     * Minimum yaw in degrees. The number is interpreted modulo 360.
     * The default value, -360, is just to make sure that we won't accidentally
     * trip it. If the number is set to something in the interval 0-360,
     * the autoRotate function will pan back and forth.
     *
     * @default -360
     * @type number
     */
    this.minYaw = -360;
    
    /**
     * Maximum yaw in degrees. The number is interpreted modulo 360.
     * The default value, 720, is just to make sure that we won't accidentally
     * trip it. If the number is set to something in the interval 0-360,
     * the autoRotate function will pan back and forth.
     *
     * @default 720.0
     * @type number
     */
    this.maxYaw = 720;
    
    /**
     * Transform offset for yaw.
     * @default 0.0
     * @type number
     */
    this.yawOffset = 0.0;
    
    /**
     * Transform offset for pitch.
     * @default 0.0
     * @type number
     */
    this.pitchOffset = 0.0;
    
    /**
     * Transform offset for roll.
     * @default 0.0
     * @type number
     */
    this.rollOffset = 0.0;
    
    /**
     * Function to call when all six cube faces have loaded the base texture level
     * and can be rendered.
     *
     * @type function()
     * @default null
     */
    this.onload = null;
    
    /**
     * The rendering back end to use.
     * Values are "css" and "webgl".
     * 
     * @type String
     * @default null
     */
    this.renderer = null;
    
    /**
     * Controls whether the panorama can be "flung" by quickly dragging and releasing.
     * 
     * @type boolean 
     * @default true
     */
    this.fling = true;
    
    /**
     * Controls the decay of the "flinging" animation. The fling animation decays
     * as 2^(-flingScale * t) where t is the time in milliseconds since the animation started.
     * For the animation to decay to half-speed in X seconds,
     * flingScale should then be set to 1 / (X*1000).
     *
     * @type float
     * @default 0.004
     */
    this.flingScale = 0.004;
    
    if (values) {
        for (var k in values) {
            this[k] = values[k];
        }
    }
    
    this.merge = function (values, overwrite) {
        for (var k in values) {
            if (overwrite || !this[k]) {
                this[k] = values[k];
            }
        }
    }
    return this;        
};
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new VR panorama in a canvas. <b>Requires WebGL or CSS3D support.</b>
 * (Note: See {@link bigshot.VRPanorama#dispose} for important information.)
 * 
 * <h3 id="creating-a-cubemap">Creating a Cube Map</h3>
 *
 * <p>The panorama consists of six image pyramids, one for each face of the VR cube.
 * Due to restrictions in WebGL, each texture tile must have a power-of-two (POT) size -
 * that is, 2, 4, ..., 128, 256, etc. Furthermore, due to the way the faces are tesselated
 * the largest image must consist of POT x POT tiles. The final restriction is that the 
 * tiles must overlap for good seamless results.
 *
 * <p>The MakeImagePyramid has some sensible defaults built-in. If you just use the
 * command line:
 *
 * <code><pre>
 * java -jar bigshot.jar input.jpg temp/dzi \
 *     --preset dzi-cubemap \ 
 *     --format folders
 * </pre></code>
 * 
 * <p>You will get 2034 pixels per face, and a tile size of 256 pixels with 2 pixels
 * overlap. If you don't like that, you can use the <code>overlap</code>, <code>face-size</code>
 * and <code>tile-size</code> parameters. Let's take these one by one:
 *
 * <ul>
 * <li><p><code>overlap</code>: Overlap defines how much tiles should overlap, just to avoid
 * seams in the rendered results caused by finite numeric precision. The default is <b>2</b>, which
 * I've found works great for me.</p></li>
 * <li><p><code>tile-size</code>: First you need to decide what POT size the output should be.
 * Then subtract the overlap value. For example, if you set overlap to 1, <code>tile-size</code>
 * could be 127, 255, 511, or any 2<sup>n</sup>-1 value.</p></li>
 * <li><p><code>face-size</code>: Finally, we decide on a size for the full cube face. This should be
 * tile-size * 2<sup>n</sup>. Let's say we set n=3, which makes each face 8x8 tiles at the most zoomed-in
 * level. For a tile-size of 255, then, face-size is 255*2<sup>3</sup> = 255*8 = <b>2040</b>.</p></li>
 * </ul>
 * 
 * <p>A command line for the hypothetical scenario above would be:
 * 
 * <code><pre>
 * java -jar bigshot.jar input.jpg temp/dzi \
 *     --preset dzi-cubemap \ 
 *     --overlap 1 \
 *     --tile-size 255 \
 *     --face-size 2040 \
 *     --format folders
 * </pre></code>
 *
 * <p>If your tile size numbers don't add up, you'll get a warning like:
 *
 * <code><pre>
 * WARNING: Resulting image tile size (tile-size + overlap) is not a power of two: 255
 * </pre></code>
 *
 * <p>If your face size don't add up, you'll get another warning:
 *
 * <code><pre>
 * WARNING: face-size is not an even multiple of tile-size: 2040 % 254 != 0
 * </pre></code>
 *
 * <h3 id="integration-with-saladoplayer">Integration With SaladoPlayer</h3>
 *
 * <p><a href="http://panozona.com/wiki/">SaladoPlayer</a> is a cool
 * Flash-based VR panorama viewer that can display Deep Zoom Images.
 * It can be used as a fallback for Bigshot for browsers that don't
 * support WebGL.
 *
 * <p>Since Bigshot can use a Deep Zoom Image (DZI) via a {@link bigshot.DeepZoomImageFileSystem}
 * adapter, the common file format is DZI. There are two cases: The first is
 * when the DZI is served up as a folder structure, the second when
 * we pack the DZI into a Bigshot archive and serve it using bigshot.php.
 *
 * <h4>Serving DZI as Folders</h4>
 *
 * <p>This is an easy one. First, we generate the required DZIs:
 *
 * <code><pre>
 * java -jar bigshot.jar input.jpg temp/dzi \
 *     --preset dzi-cubemap \ 
 *     --format folders
 * </pre></code>
 * 
 * <p>We'll assume that we have the six DZI folders in "temp/dzi", and that
 * they have "face_" as a common prefix (which is what Bigshot's MakeImagePyramid
 * outputs). So we have, for example, "temp/dzi/face_f.xml" and the tiles for face_f
 * in "temp/dzi/face_f/". Set up Bigshot like this:
 *
 * <code><pre>
 * bvr = new bigshot.VRPanorama (
 *     new bigshot.VRPanoramaParameters ({
 *             container : document.getElementById ("canvas"),
 *             basePath : "temp/dzi",
 *             fileSystemType : "dzi"
 *         }));
 * </pre></code>
 * 
 * <p>SaladoPlayer uses an XML config file, which in this case will
 * look something like this:
 * 
 * <code><pre>
 * &lt;SaladoPlayer>
 *     &lt;global debug="false" firstPanorama="pano"/>
 *     &lt;panoramas>
 *         &lt;panorama id="pano" path="temp/dzi/face_f.xml"/>
 *     &lt;/panoramas>
 * &lt;/SaladoPlayer>
 * </pre></code>
 *
 * <h4>Serving DZI as Archive</h4>
 *
 * <p>This one is a bit more difficult. First we create a DZI as a bigshot archive:
 *
 * <code><pre>
 * java -jar bigshot.jar input.jpg temp/dzi.bigshot \
 *     --preset dzi-cubemap \ 
 *     --format archive
 * </pre></code>
 *
 * <p>We'll assume that we have our Bigshot archive at
 * "temp/dzi.bigshot". For this we will use the "entry" parameter of bigshot.php
 * to serve up the right files:
 *
 * <code><pre>
 * bvr = new bigshot.VRPanorama (
 *     new bigshot.VRPanoramaParameters ({
 *             container : document.getElementById ("canvas"),
 *             basePath : "/bigshot.php?file=temp/dzi.bigshot&entry=",
 *             fileSystemType : "dzi"
 *         }));
 * </pre></code>
 * 
 * <p>SaladoPlayer uses an XML config file, which in this case will
 * look something like this:
 * 
 * <code><pre>
 * &lt;SaladoPlayer>
 *     &lt;global debug="false" firstPanorama="pano"/>
 *     &lt;panoramas>
 *         &lt;panorama id="pano" path="/bigshot.php?file=dzi.bigshot&amp;amp;entry=face_f.xml"/>
 *     &lt;/panoramas>
 * &lt;/SaladoPlayer>
 * </pre></code>
 *
 * <h3>Usage example:</h3>
 * @example
 * var bvr = new bigshot.VRPanorama (
 *     new bigshot.VRPanoramaParameters ({
 *             basePath : "/bigshot.php?file=myvr.bigshot",
 *             fileSystemType : "archive",
 *             container : document.getElementById ("bigshot_canvas")
 *         }));
 * @class A cube-map VR panorama.
 * @extends bigshot.EventDispatcher
 *
 * @param {bigshot.VRPanoramaParameters} parameters the panorama parameters.
 *
 * @see bigshot.VRPanoramaParameters
 */
bigshot.VRPanorama = function (parameters) {
    bigshot.EventDispatcher.call (this);
    
    var that = this;
    
    this.parameters = parameters;
    this.maxTextureMagnification = parameters.maxTextureMagnification;
    this.container = parameters.container;
    this.browser = new bigshot.Browser ();
    this.dragStart = null;
    this.dragDistance = 0;
    this.hotspots = [];
    this.disposed = false;
    
    this.transformOffsets = {
        y : parameters.yawOffset,
        p : parameters.pitchOffset,
        r : parameters.rollOffset
    };
    
    /**
     * Current camera state.
     * @private
     */
    this.state = {
        rotation : {
            /**
             * Pitch in degrees.
             * @type float
             * @private
             */
            p : 0.0,
            
            /**
             * Yaw in degrees.
             * @type float
             * @private
             */
            y : 0.0,
            
            r : 0
        },
        
        /**
         * Field of view (vertical) in degrees.
         * @type float
         * @private
         */
        fov : 45,
        
        translation : {
            /**
             * Translation along X-axis.
             * @private
             * @type float
             */
            x : 0.0,
            
            /**
             * Translation along Y-axis.
             * @private
             * @type float
             */
            y : 0.0,
            
            /**
             * Translation along Z-axis.
             * @private
             * @type float
             */
            z : 0.0
        }
    };
    
    /**
     * Renderer wrapper.
     * @private
     * @type bigshot.VRRenderer
     */
    this.renderer = null;
    if (this.parameters.renderer) {
        if (this.parameters.renderer == "css") {
            this.renderer = new bigshot.CSS3DVRRenderer (this.container);
        } else if (this.parameters.renderer == "webgl") {
            this.renderer = new bigshot.WebGLVRRenderer (this.container)
        } else {
            throw new Error ("Unknown renderer: " + this.parameters.renderer);
        }
    } else {
        this.renderer = 
            bigshot.WebGLUtil.isWebGLSupported () ? 
        new bigshot.WebGLVRRenderer (this.container)
        :
        new bigshot.CSS3DVRRenderer (this.container);
    }
    
    /**
     * List of render listeners to call at the start and end of each render.
     *
     * @private
     */
    this.renderListeners = new Array (); 
    
    this.renderables = new Array ();
    
    /**
     * Current value of the idle counter.
     *
     * @private
     */
    this.idleCounter = 0;
    
    /**
     * Maximum value of the idle counter before any idle events start,
     * such as autorotation.
     *
     * @private
     */
    this.maxIdleCounter = -1;
    
    
    /**
     * Integer acting as a "permit". When the smoothRotate function
     * is called, the current value is incremented and saved. If the number changes
     * that particular call to smoothRotate stops. This way we avoid
     * having multiple smoothRotate rotations going in parallel.
     * @private
     * @type int
     */
    this.smoothrotatePermit = 0;
    
    /**
     * Helper function to consume events.
     * @private
     */
    var consumeEvent = function (event) {
        if (event.preventDefault) {
            event.preventDefault ();
        }
        return false;
    };
    
    /**
     * Full screen handler.
     *
     * @private
     */
    this.fullScreenHandler = null;
    
    this.renderAsapPermitTaken = false;
    
    /**
     * An element to use as reference when resizing the canvas element.
     * If non-null, any onresize() calls will result in the canvas being
     * resized to the size of this element.
     *
     * @private
     */
    this.sizeContainer = null;
    
    /**
     * The six cube faces.
     *
     * @type bigshot.VRFace[]
     * @private
     */
    var facesInit = {
        facesLeft : 6,
        faceLoaded : function () {
            this.facesLeft--;
            if (this.facesLeft == 0) {
                if (that.parameters.onload) {
                    that.parameters.onload ();
                }
            }
        }
    };
    var onFaceLoad = function () { 
        facesInit.faceLoaded () 
    };
    
    this.vrFaces = new Array ();
    this.vrFaces[0] = new bigshot.VRFace (this, "f", {x:-1, y:1, z:-1}, 2.0, {x:1, y:0, z:0}, {x:0, y:-1, z:0}, onFaceLoad);
    this.vrFaces[1] = new bigshot.VRFace (this, "b", {x:1, y:1, z:1}, 2.0, {x:-1, y:0, z:0}, {x:0, y:-1, z:0}, onFaceLoad);
    this.vrFaces[2] = new bigshot.VRFace (this, "l", {x:-1, y:1, z:1}, 2.0, {x:0, y:0, z:-1}, {x:0, y:-1, z:0}, onFaceLoad);
    this.vrFaces[3] = new bigshot.VRFace (this, "r", {x:1, y:1, z:-1}, 2.0, {x:0, y:0, z:1}, {x:0, y:-1, z:0}, onFaceLoad);
    this.vrFaces[4] = new bigshot.VRFace (this, "u", {x:-1, y:1, z:1}, 2.0, {x:1, y:0, z:0}, {x:0, y:0, z:-1}, onFaceLoad);
    this.vrFaces[5] = new bigshot.VRFace (this, "d", {x:-1, y:-1, z:-1}, 2.0, {x:1, y:0, z:0}, {x:0, y:0, z:1}, onFaceLoad);
    
    /**
     * Helper function to translate touch events to mouse-like events.
     * @private
     */
    var translateEvent = function (event) {
        if (event.clientX) {
            return event;
        } else {
            return {
                clientX : event.changedTouches[0].clientX,
                clientY : event.changedTouches[0].clientY
            };
        };
    };
    
    this.lastTouchStartAt = -1;
    
    this.allListeners = {
        "mousedown" : function (e) {
            that.smoothRotate ();
            that.resetIdle ();            
            that.dragMouseDown (e);
            return consumeEvent (e);
        },
        "mouseup" : function (e) {
            that.resetIdle ();
            that.dragMouseUp (e);
            return consumeEvent (e);
        },
        "mousemove" : function (e) {
            that.resetIdle ();
            that.dragMouseMove (e);
            return consumeEvent (e);
        },
        "gesturestart" : function (e) {
            that.gestureStart (e);
            return consumeEvent (e);
        },
        "gesturechange" : function (e) {
            that.gestureChange (e);
            return consumeEvent (e);
        },
        "gestureend" : function (e) {
            that.gestureEnd (e);
            return consumeEvent (e);
        },
        
        "DOMMouseScroll" : function (e) {
            that.resetIdle ();
            that.mouseWheel (e);
            return consumeEvent (e);
        },
        "mousewheel" : function (e) {
            that.resetIdle ();
            that.mouseWheel (e);
            return consumeEvent (e);
        },
        "dblclick" : function (e) {
            that.mouseDoubleClick (e);
            return consumeEvent (e);
        },
        
        "touchstart" : function (e) {
            that.smoothRotate ();
            that.lastTouchStartAt = new Date ().getTime ();
            that.resetIdle ();
            that.dragMouseDown (translateEvent (e));
            return consumeEvent (e);
        },
        "touchend" : function (e) {
            that.resetIdle ();
            var handled = that.dragMouseUp (translateEvent (e));
            if (!handled && (that.lastTouchStartAt > new Date().getTime() - 350)) {
                that.mouseDoubleClick (translateEvent (e));
            }
            that.lastTouchStartAt = -1;
            return consumeEvent (e);
        },
        "touchmove" : function (e) {
            if (that.dragDistance > 24) {                
                that.lastTouchStartAt = -1;
            }
            that.resetIdle ();
            that.dragMouseMove (translateEvent (e));
            return consumeEvent (e);
        }
    };
    this.addEventListeners ();
    
    /**
     * Stub function to call onresize on this instance.
     *
     * @private
     */
    this.onresizeHandler = function (e) {
        that.onresize ();
    };
    
    this.browser.registerListener (window, 'resize', this.onresizeHandler, false);
    this.browser.registerListener (document.body, 'orientationchange', this.onresizeHandler, false);
    
    this.setPitch (0.0);
    this.setYaw (0.0);
    this.setFov (45.0);
}

/*
 * Statics
 */

/**
 * When the mouse is pressed and dragged, the camera rotates
 * proportionally to the length of the dragging.
 *
 * @constant
 * @public
 * @static
 */
bigshot.VRPanorama.DRAG_GRAB = "grab";

/**
 * When the mouse is pressed and dragged, the camera continuously
 * rotates with a speed that is proportional to the length of the 
 * dragging.
 *
 * @constant
 * @public
 * @static
 */
bigshot.VRPanorama.DRAG_PAN = "pan";

/**
 * @name bigshot.VRPanorama.RenderState
 * @class The state the renderer is in when a {@link bigshot.VRPanorama.RenderListener} is called.
 *
 * @see bigshot.VRPanorama.ONRENDER_BEGIN
 * @see bigshot.VRPanorama.ONRENDER_END
 */

/**
 * A RenderListener state parameter value used at the start of each render.
 * 
 * @constant
 * @public
 * @static
 * @type bigshot.VRPanorama.RenderState
 */
bigshot.VRPanorama.ONRENDER_BEGIN = 0;

/**
 * A RenderListener state parameter value used at the end of each render.
 * 
 * @constant
 * @public
 * @static
 * @type bigshot.VRPanorama.RenderState
 */
bigshot.VRPanorama.ONRENDER_END = 1;

/**
 * A RenderListener cause parameter indicating that a previously requested 
 * texture has loaded and a render is forced. The data parameter is not used.
 *
 * @constant
 * @public
 * @static
 * @param {bigshot.VRPanorama.RenderCause}
 */
bigshot.VRPanorama.ONRENDER_TEXTURE_UPDATE = 0;

/**
 * @name bigshot.VRPanorama.RenderCause
 * @class The reason why the {@link bigshot.VRPanorama} is being rendered.
 * Due to the events outside of the panorama, the VR panorama may be forced to
 * re-render itself. When this happens, the {@link bigshot.VRPanorama.RenderListener}s
 * receive a constant indicating the cause of the rendering.
 * 
 * @see bigshot.VRPanorama.ONRENDER_TEXTURE_UPDATE
 */

/**
 * Specification for functions passed to {@link bigshot.VRPanorama#addRenderListener}.
 *
 * @name bigshot.VRPanorama.RenderListener
 * @function
 * @param {bigshot.VRPanorama.RenderState} state The state of the renderer. Can be {@link bigshot.VRPanorama.ONRENDER_BEGIN} or {@link bigshot.VRPanorama.ONRENDER_END}
 * @param {bigshot.VRPanorama.RenderCause} [cause] The reason for rendering the scene. Can be undefined or {@link bigshot.VRPanorama.ONRENDER_TEXTURE_UPDATE}
 * @param {Object} [data] An optional data object that is dependent on the cause. See the documentation 
 *             for the different causes.
 */

/**
 * Specification for functions passed to {@link bigshot.VRPanorama#addRenderable}.
 *
 * @name bigshot.VRPanorama.Renderable
 * @function
 * @param {bigshot.VRRenderer} renderer The renderer object to use.
 * @param {bigshot.TexturedQuadScene} scene The scene to render into.
 */

/** */
bigshot.VRPanorama.prototype = {
    /**
     * Adds a hotstpot.
     *
     * @param {bigshot.VRHotspot} hs the hotspot to add
     */
    addHotspot : function (hs) {
        this.hotspots.push (hs);
    },
    
    /**
     * Returns the {@link bigshot.VRPanoramaParameters} object used by this instance.
     *
     * @type bigshot.VRPanoramaParameters
     */
    getParameters : function () {
        return this.parameters;
    },
    
    /**
     * Sets the view translation.
     *
     * @param x translation of the viewer along the X axis
     * @param y translation of the viewer along the Y axis
     * @param z translation of the viewer along the Z axis
     */
    setTranslation : function (x, y, z) {
        this.state.translation.x = x;
        this.state.translation.y = y;
        this.state.translation.z = z;
    },
    
    /**
     * Returns the current view translation as an x-y-z triplet.
     *
     * @returns {number} x translation of the viewer along the X axis
     * @returns {number} y translation of the viewer along the Y axis
     * @returns {number} z translation of the viewer along the Z axis
     */
    getTranslation : function () {
        return this.state.translation;
    },
    
    /**
     * Sets the field of view.
     *
     * @param {number} fov the vertical field of view, in degrees
     */
    setFov : function (fov) {
        fov = Math.min (this.parameters.maxFov, fov);
        fov = Math.max (this.parameters.minFov, fov);
        this.state.fov = fov;
    },
    
    /**
     * Gets the field of view.
     *
     * @return {number} the vertical field of view, in degrees
     */
    getFov : function () {
        return this.state.fov;
    },
    
    /**
     * Returns the angle (yaw, pitch) for a given pixel coordinate.
     *
     * @param {number} x the x-coordinate of the pixel, measured in pixels 
     *                 from the left edge of the panorama.
     * @param {number} y the y-coordinate of the pixel, measured in pixels 
     *                 from the top edge of the panorama.
     * @return {number} .yaw the yaw angle of the pixel (0 &lt;= yaw &lt; 360)
     * @return {number} .pitch the pitch angle of the pixel (-180 &lt;= pitch &lt;= 180)
     *
     * @example
     * var container = ...; // an HTML element
     * var pano = ...; // a bigshot.VRPanorama
     * ...
     * container.addEventListener ("click", function (e) {
     *     var clickX = e.clientX - container.offsetX;
     *     var clickY = e.clientY - container.offsetY;
     *     var polar = pano.screenToPolar (clickX, clickY);
     *     alert ("You clicked at: " + 
     *            "Yaw: " + polar.yaw + 
     *            "  Pitch: " + polar.pitch);
     * });
     */
    screenToPolar : function (x, y) {
        var dray = this.screenToRayDelta (x, y);
        var ray = $V([dray.x, dray.y, dray.z, 1.0]);
        
        ray = Matrix.RotationX (this.getPitch () * Math.PI / 180.0).ensure4x4 ().x (ray);
        ray = Matrix.RotationY (-this.getYaw () * Math.PI / 180.0).ensure4x4 ().x (ray);
        
        var dx = ray.e(1);
        var dy = ray.e(2);
        var dz = ray.e(3);
        
        var dxz = Math.sqrt (dx * dx + dz * dz);
        
        var dyaw = Math.atan2 (dx, -dz) * 180 / Math.PI;
        var dpitch = Math.atan2 (dy, dxz) * 180 / Math.PI;
        
        var res = {};
        res.yaw = (dyaw + 360) % 360.0;
        res.pitch = dpitch;
        
        return res;
    },
    
    /**
     * Restricts the pitch value to be between the minPitch and maxPitch parameters.
     * 
     * @param {number} p the pitch value
     * @returns the constrained pitch value.
     */
    snapPitch : function (p) {
        p = Math.min (this.parameters.maxPitch, p);
        p = Math.max (this.parameters.minPitch, p);
        return p;
    },
    
    /**
     * Sets the current camera pitch.
     *
     * @param {number} p the pitch, in degrees
     */
    setPitch : function (p) {
        this.state.rotation.p = this.snapPitch (p);
    },
    
    /**
     * Subtraction mod 360, sort of...
     *
     * @private
     * @returns the angular distance with smallest magnitude to add to p0 to get to p1 % 360
     */
    circleDistance : function (p0, p1) {
        if (p1 > p0) {
            // p1 is somewhere clockwise to p0
            var d1 = (p1 - p0); // move clockwise
            var d2 = ((p1 - 360) - p0); // move counterclockwise, first -p0 to get to 0, then p1 - 360.
            return Math.abs (d1) < Math.abs (d2) ? d1 : d2;
        } else {
            // p1 is somewhere counterclockwise to p0
            var d1 = (p1 - p0); // move counterclockwise
            var d2 = (360 - p0) + p1; // move clockwise, first (360-p= to get to 0, then another p1 degrees
            return Math.abs (d1) < Math.abs (d2) ? d1 : d2;
        }
    },
    
    /**
     * Subtraction mod 360, sort of...
     *
     * @private
     */
    circleSnapTo : function (p, p1, p2) {
        var d1 = this.circleDistance (p, p1);
        var d2 = this.circleDistance (p, p2);
        return Math.abs (d1) < Math.abs (d2) ? p1 : p2;
    },
    
    /**
     * Constrains a yaw value to the required minimum and maximum values.
     *
     * @private
     */
    snapYaw : function (y) {
        y %= 360;
        if (y < 0) {
            y += 360;
        }
        if (this.parameters.minYaw < this.parameters.maxYaw) {
            if (y > this.parameters.maxYaw || y < this.parameters.minYaw) {
                y = circleSnapTo (y, this.parameters.minYaw, this.parameters.maxYaw);
            }
        } else {
            // The only time when minYaw > maxYaw is when the interval
            // contains the 0 angle.
            if (y > this.parameters.minYaw) {
                // ok, we're somewhere between minYaw and 0.0
            } else if (y > this.parameters.maxYaw) {
                // we're somewhere between maxYaw and minYaw 
                // (but on the wrong side).
                // figure out the nearest point and snap to it
                y = circleSnapTo (y, this.parameters.minYaw, this.parameters.maxYaw);
            } else {
                // ok, we're somewhere between 0.0 and maxYaw
            }
        }
        return y;
    },
    
    /**
     * Sets the current camera yaw. The yaw is normalized between
     * 0 <= y < 360.
     *
     * @param {number} y the yaw, in degrees
     */
    setYaw : function (y) {
        this.state.rotation.y = this.snapYaw (y);
    },
    
    /**
     * Gets the current camera yaw.
     *
     * @return {number} the yaw, in degrees
     */
    getYaw : function () {
        return this.state.rotation.y;
    },
    
    /**
     * Gets the current camera pitch.
     *
     * @return {number} the pitch, in degrees
     */
    getPitch : function () {
        return this.state.rotation.p;
    },
    
    /**
     * Unregisters event handlers and other page-level hooks. The client need not call this
     * method unless bigshot images are created and removed from the page
     * dynamically. In that case, this method must be called when the client wishes to
     * free the resources allocated by the image. Otherwise the browser will garbage-collect
     * all resources automatically.
     * @public
     */
    dispose : function () {
        this.disposed = true;
        this.browser.unregisterListener (window, "resize", this.onresizeHandler, false);
        this.browser.unregisterListener (document.body, "orientationchange", this.onresizeHandler, false);
        this.removeEventListeners ();
        
        for (var i = 0; i < this.vrFaces.length; ++i) {
            this.vrFaces[i].dispose ();
        }
        this.renderer.dispose ();
    },
    
    /**
     * Creates and initializes a {@link bigshot.VREvent} object.
     * The {@link bigshot.VREvent#ray}, {@link bigshot.VREvent#yaw},
     * {@link bigshot.VREvent#pitch}, {@link bigshot.Event#target} and
     * {@link bigshot.Event#currentTarget} fields are set.
     * 
     * @param {Object} data the data object for the event
     * @param {number} data.clientX the client x-coordinate of the event
     * @param {number} data.clientY the client y-coordinate of the event
     * @returns the new event object
     * @type bigshot.VREvent
     */
    createVREventData : function (data) {
        var elementPos = this.browser.getElementPosition (this.container);
        data.localX = data.clientX - elementPos.x;
        data.localY = data.clientY - elementPos.y;
        
        data.ray = this.screenToRay (data.localX, data.localY);
        
        var polar = this.screenToPolar (data.localX, data.localY);
        data.yaw = polar.yaw;
        data.pitch = polar.pitch;
        data.target = this;
        data.currentTarget = this;
        
        return new bigshot.VREvent (data);
    },
    
    
    /**
     * Sets up transformation matrices etc. Calls all render listeners with a state parameter
     * of {@link bigshot.VRPanorama.ONRENDER_BEGIN}.
     *
     * @private
     *
     * @param [cause] parameter for the {@link bigshot.VRPanorama.RenderListener}s.
     * @param [data] parameter for the {@link bigshot.VRPanorama.RenderListener}s.
     */
    beginRender : function (cause, data) {
        this.onrender (bigshot.VRPanorama.ONRENDER_BEGIN, cause, data);
        this.renderer.beginRender (this.state.rotation, this.state.fov, this.state.translation, this.transformOffsets);
    },
    
    
    /**
     * Add a function that will be called at various times during the render.
     *
     * @param {bigshot.VRPanorama.RenderListener} listener the listener function
     */
    addRenderListener : function (listener) {
        var rl = new Array ();
        rl = rl.concat (this.renderListeners);
        rl.push (listener);
        this.renderListeners = rl;
    },
    
    /**
     * Removes a function that will be called at various times during the render.
     *
     * @param {bigshot.VRPanorama.RenderListener} listener the listener function
     */
    removeRenderListener : function (listener) {
        var rl = new Array ();
        rl = rl.concat (this.renderListeners);
        for (var i = 0; i < rl.length; ++i) {
            if (rl[i] === listener) {
                rl.splice (i, 1);
                break;
            }
        }
        this.renderListeners = rl;
    },
    
    /**
     * Called at the start and end of every render.
     *
     * @event
     * @private
     * @type function()
     * @param {bigshot.VRPanorama.RenderState} state the current render state
     */
    onrender : function (state, cause, data) {
        var rl = this.renderListeners;
        for (var i = 0; i < rl.length; ++i) {
            rl[i](state, cause, data);
        }
    },
    
    /**
     * Performs per-render cleanup. Calls all render listeners with a state parameter
     * of {@link bigshot.VRPanorama.ONRENDER_END}.
     *
     * @private
     * 
     * @param [cause] parameter for the {@link bigshot.VRPanorama.RenderListener}s.
     * @param [data] parameter for the {@link bigshot.VRPanorama.RenderListener}s.
     */
    endRender : function (cause, data) {
        for (var f in this.vrFaces) {
            this.vrFaces[f].endRender ();
        }
        this.renderer.endRender ();
        this.onrender (bigshot.VRPanorama.ONRENDER_END, cause, data);
    },
    
    /**
     * Add a function that will be called to render any additional quads.
     *
     * @param {bigshot.VRPanorama.Renderable} renderable The renderable, a function responsible for
     * rendering additional scene elements.
     */
    addRenderable : function (renderable) {
        var rl = new Array ();
        rl.concat (this.renderables);
        rl.push (renderable);
        this.renderables = rl;
    },
    
    /**
     * Removes a function that will be called to render any additional quads.
     *
     * @param {bigshot.VRPanorama.Renderable} renderable The renderable added using
     * {@link bigshot.VRPanorama#addRenderable}.
     */
    removeRenderable : function (renderable) {
        var rl = new Array ();
        rl.concat (this.renderables);
        for (var i = 0; i < rl.length; ++i) {
            if (rl[i] == listener) {
                rl.splice (i, 1);
                break;
            }
        }
        this.renderables = rl;
    },
    
    /**
     * Renders the VR cube.
     *
     * @param [cause] parameter for the {@link bigshot.VRPanorama.RenderListener}s.
     * @param [data] parameter for the {@link bigshot.VRPanorama.RenderListener}s.
     */
    render : function (cause, data) {
        if (!this.disposed) {
            this.beginRender (cause, data);
            
            var scene = this.renderer.createTexturedQuadScene ();
            
            for (var f in this.vrFaces) {
                this.vrFaces[f].render (scene);
            }
            
            for (var i = 0; i < this.renderables.length; ++i) {
                this.renderables[i](this.renderer, scene);
            }
            
            scene.render ();
            
            for (var i = 0; i < this.hotspots.length; ++i) {
                this.hotspots[i].layout ();
            }
            
            this.endRender (cause, data);
        }
    },
    
    /**
     * Render updated faces. Called as tiles are loaded from the server.
     *
     * @param [cause] parameter for the {@link bigshot.VRPanorama.RenderListener}s.
     * @param [data] parameter for the {@link bigshot.VRPanorama.RenderListener}s.
     */
    renderUpdated : function (cause, data) {
        if (!this.disposed && this.renderer.supportsUpdate ()) {
            this.beginRender (cause, data);
            
            var scene = this.renderer.createTexturedQuadScene ();
            
            for (var f in this.vrFaces) {
                if (this.vrFaces[f].isUpdated ()) {
                    this.vrFaces[f].render (scene);
                }
            }
            
            scene.render ();
            
            for (var i = 0; i < this.hotspots.length; ++i) {
                this.hotspots[i].layout ();
            }
            
            this.endRender (cause, data);
        } else {
            this.render (cause, data);
        }
    },
    
    /**
     * The current drag mode.
     * 
     * @private
     */
    dragMode : bigshot.VRPanorama.DRAG_GRAB,
    
    /**
     * Sets the mouse dragging mode.
     *
     * @param mode one of {@link bigshot.VRPanorama.DRAG_PAN} or {@link bigshot.VRPanorama.DRAG_GRAB}.
     */
    setDragMode : function (mode) {
        this.dragMode = mode;
    },
    
    addEventListeners : function () {
        for (var k in this.allListeners) {
            this.browser.registerListener (this.container, k, this.allListeners[k], false);
        }
    },
    
    removeEventListeners : function () {
        for (var k in this.allListeners) {
            this.browser.unregisterListener (this.container, k, this.allListeners[k], false);
        }
    },
    
    dragMouseDown : function (e) {
        this.dragStart = {
            clientX : e.clientX,
            clientY : e.clientY
        };
        this.dragLast = {
            clientX : e.clientX,
            clientY : e.clientY,
            dx : 0,
            dy : 0,
            dt : 1000000,
            time : new Date ().getTime ()
        };
        this.dragDistance = 0;
    },
    
    dragMouseUp : function (e) {
        // In case we got a mouse up with out a previous mouse down,
        // for example, double-click on title bar to maximize the 
        // window
        if (this.dragStart == null || this.dragLast == null) {
            this.dragStart = null;
            this.dragLast = null;
            return;
        }
        
        this.dragStart = null;
        var dx = this.dragLast.dx;
        var dy = this.dragLast.dy;
        var ds = Math.sqrt (dx * dx + dy * dy);
        var dt = this.dragLast.dt;
        var dtb = new Date ().getTime () - this.dragLast.time;
        this.dragLast = null;
        
        var v = dt > 0 ? (ds / dt) : 0;
        if (v > 0.05 && dtb < 250 && dt > 20 && this.parameters.fling) {
            var scale = this.state.fov / this.renderer.getViewportHeight ();
            
            var t0 = new Date ().getTime ();
            
            var flingScale = this.parameters.flingScale;
            
            dx /= dt;
            dy /= dt;
            
            this.smoothRotate (function (dat) {
                    var dt = new Date ().getTime () - t0;
                    var fact = Math.pow (2, -dt * flingScale);
                    var d = (dx * dat * scale) * fact;
                    return fact > 0.01 ? d : null;
                }, function (dat) {
                    var dt = new Date ().getTime () - t0;
                    var fact = Math.pow (2, -dt * flingScale);
                    var d = (dy * dat * scale) * fact;
                    return fact > 0.01 ? d : null;
                }, function () {
                    return null;
                });
            return true;
        } else {
            this.smoothRotate ();
            return false;
        }
    },
    
    dragMouseMove : function (e) {
        if (this.dragStart != null && this.currentGesture == null) {
            if (this.dragMode == bigshot.VRPanorama.DRAG_GRAB) {
                this.smoothRotate ();
                var scale = this.state.fov / this.renderer.getViewportHeight ();
                var dx = e.clientX - this.dragStart.clientX;
                var dy = e.clientY - this.dragStart.clientY;
                this.dragDistance += dx + dy;
                this.setYaw (this.getYaw () - dx * scale);
                this.setPitch (this.getPitch () - dy * scale);
                this.renderAsap ();
                this.dragStart = e;
                var dt = new Date ().getTime () - this.dragLast.time;
                if (dt > 20) {
                    this.dragLast = {
                        dx : this.dragLast.clientX - e.clientX,
                        dy : this.dragLast.clientY - e.clientY,
                        dt : dt,
                        clientX : e.clientX,
                        clientY : e.clientY,
                        time : new Date ().getTime ()
                    };
                }
            } else {
                var scale = 0.1 * this.state.fov / this.renderer.getViewportHeight ();
                var dx = e.clientX - this.dragStart.clientX;
                var dy = e.clientY - this.dragStart.clientY;
                this.dragDistance = dx + dy;
                this.smoothRotate (
                    function () {
                        return dx * scale;
                    },
                    function () {
                        return dy * scale;
                    });
            }
        }
    },
    
    onMouseDoubleClick : function (e, x, y) {
        var eventData = this.createVREventData ({
                type : "dblclick",
                clientX : e.clientX,
                clientY : e.clientY
            });
        this.fireEvent ("dblclick", eventData);
        if (!eventData.defaultPrevented) {
            this.smoothRotateToXY (x, y);
        }
    },
    
    mouseDoubleClick : function (e) {
        var pos = this.browser.getElementPosition (this.container);
        this.onMouseDoubleClick (e, e.clientX - pos.x, e.clientY - pos.y);
    },
    
    /**
     * Begins a potential drag event.
     *
     * @private
     */
    gestureStart : function (event) {
        this.currentGesture = {
            startFov : this.getFov (),
            scale : event.scale
        };            
    },
    
    /**
     * Begins a potential drag event.
     *
     * @private
     */
    gestureEnd : function (event) {
        this.currentGesture = null;
    },
    
    /**
     * Begins a potential drag event.
     *
     * @private
     */
    gestureChange : function (event) {
        if (this.currentGesture) {
            var newFov = this.currentGesture.startFov / event.scale;
            this.setFov (newFov);
            this.renderAsap ();
        }
    },
    
    /**
     * Sets the maximum texture magnification.
     *
     * @param {number} v the maximum texture magnification
     * @see bigshot.VRPanoramaParameters#maxTextureMagnification
     */
    setMaxTextureMagnification : function (v) {
        this.maxTextureMagnification = v;
    },
    
    /**
     * Gets the current maximum texture magnification.
     *
     * @type number
     * @see bigshot.VRPanoramaParameters#maxTextureMagnification
     */
    getMaxTextureMagnification : function () {
        return this.maxTextureMagnification;
    },
    
    /**
     * Computes the minimum field of view where the resulting image will not
     * have to stretch the textures more than given by the
     * {@link bigshot.VRPanoramaParameters#maxTextureMagnification} parameter.
     *
     * @type number
     * @return the minimum FOV, below which it is necessary to stretch the 
     * vr cube texture more than the given {@link bigshot.VRPanoramaParameters#maxTextureMagnification}
     */
    getMinFovFromViewportAndImage : function () {
        var halfHeight = this.renderer.getViewportHeight () / 2;
        
        var minFaceHeight = this.vrFaces[0].parameters.height;
        for (var i in this.vrFaces) {
            minFaceHeight = Math.min (minFaceHeight, this.vrFaces[i].parameters.height);
        }
        
        var edgeSizeY = this.maxTextureMagnification * minFaceHeight / 2;
        
        var wy = halfHeight / edgeSizeY;
        
        var mz = Math.atan (wy) * 180 / Math.PI;
        
        return mz * 2;
    },
    
    /**
     * Transforms screen coordinates to a world-coordinate ray.
     * @private
     */
    screenToRay : function (x, y) {
        var dray = this.screenToRayDelta (x, y);
        var ray = this.renderer.transformToWorld (dray);
        ray = Matrix.RotationY (-this.transformOffsets.y * Math.PI / 180.0).ensure4x4 ().xPoint3Dhom1 (ray);
        ray = Matrix.RotationX (-this.transformOffsets.p * Math.PI / 180.0).ensure4x4 ().xPoint3Dhom1 (ray);
        ray = Matrix.RotationZ (-this.transformOffsets.r * Math.PI / 180.0).ensure4x4 ().xPoint3Dhom1 (ray);
        return ray;
    },
    
    /**
     * @private
     */
    screenToRayDelta : function (x, y) {
        var halfHeight = this.renderer.getViewportHeight () / 2;
        var halfWidth = this.renderer.getViewportWidth () / 2;
        var x = (x - halfWidth);
        var y = (y - halfHeight);
        
        var edgeSizeY = Math.tan ((this.state.fov / 2) * Math.PI / 180);
        var edgeSizeX = edgeSizeY * this.renderer.getViewportWidth () / this.renderer.getViewportHeight ();
        
        var wx = x * edgeSizeX / halfWidth;
        var wy = y * edgeSizeY / halfHeight;
        var wz = -1.0;
        
        return {
            x : wx,
            y : wy,
            z : wz
        };
    },
    
    /**
     * Smoothly rotates the panorama so that the 
     * point given by x and y, in pixels relative to the top left corner
     * of the panorama, ends up in the center of the viewport.
     *
     * @param {int} x the x-coordinate, in pixels from the left edge
     * @param {int} y the y-coordinate, in pixels from the top edge
     */
    smoothRotateToXY : function (x, y) {
        var polar = this.screenToPolar (x, y);
        
        this.smoothRotateTo (this.snapYaw (polar.yaw), this.snapPitch (polar.pitch), this.getFov (), this.state.fov / 200);
    },
    
    /**
     * Gives the step to take to slowly approach the 
     * target value.
     *
     * @example
     * current = current + this.ease (current, target, 1.0);
     * @private
     */
    ease : function (current, target, speed, snapFrom) {
        var easingFrom = speed * 40;
        if (!snapFrom) {
            snapFrom = speed / 5;
        }
        var ignoreFrom = speed / 1000;
        
        var distance = current - target;
        if (distance > easingFrom) {
            distance = -speed;
        } else if (distance < -easingFrom) {
            distance = speed;
        } else if (Math.abs (distance) < snapFrom) {
            distance = -distance;
        } else if (Math.abs (distance) < ignoreFrom) {
            distance = 0;
        } else {
            distance = - (speed * distance) / (easingFrom);
        }
        return distance;
    },
    
    /**
     * Resets the "idle" clock.
     * @private
     */
    resetIdle : function () {
        this.idleCounter = 0;
    },
    
    /**
     * Idle clock.
     * @private
     */
    idleTick : function () {
        if (this.maxIdleCounter < 0) {
            return;
        }
        ++this.idleCounter;
        if (this.idleCounter == this.maxIdleCounter) {
            this.autoRotate ();
        }
        var that = this;
        setTimeout (function () {
                that.idleTick ();
            }, 1000);
    },
    
    /**
     * Sets the panorama to auto-rotate after a certain time has
     * elapsed with no user interaction. Default is disabled.
     * 
     * @param {int} delay the delay in seconds. Set to < 0 to disable
     * auto-rotation when idle
     */
    autoRotateWhenIdle : function (delay) {
        this.maxIdleCounter = delay;
        this.idleCounter = 0;
        if (delay < 0) {
            return;
        } else if (this.maxIdleCounter > 0) {            
            var that = this;
            setTimeout (function () {
                    that.idleTick ();
                }, 1000);
        }
    },
    
    /**
     * Starts auto-rotation of the camera. If the yaw is constrained,
     * will pan back and forth between the yaw endpoints. Call
     * {@link #smoothRotate}() to stop the rotation.
     */
    autoRotate : function () {
        var that = this;
        var scale = this.state.fov / 400;
        
        var speed = scale;
        var dy = speed;
        this.smoothRotate (
            function () {
                var nextPos = that.getYaw () + dy;
                if (that.parameters.minYaw < that.parameters.maxYaw) {
                    if (nextPos > that.parameters.maxYaw || nextPos < that.parameters.minYaw) {
                        dy = -dy;
                    }
                } else {
                    // The only time when minYaw > maxYaw is when the interval
                    // contains the 0 angle.
                    if (nextPos > that.parameters.minYaw) {
                        // ok, we're somewhere between minYaw and 0.0
                    } else if (nextPos > that.parameters.maxYaw) {
                        dy = -dy;
                    } else {
                        // ok, we're somewhere between 0.0 and maxYaw
                    }
                }
                return dy;
            }, function () {
                return that.ease (that.getPitch (), 0.0, speed);
            }, function () {
                return that.ease (that.getFov (), 45.0, 0.1);
            });
    },
    
    /**
     * Smoothly rotates the panorama to the given state.
     *
     * @param {number} yaw the target yaw
     * @param {number} pitch the target pitch
     * @param {number} fov the target vertical field of view
     * @param {number} the speed to rotate with
     */
    smoothRotateTo : function (yaw, pitch, fov, speed) {
        var that = this;
        this.smoothRotate (
            function () {
                var distance = that.circleDistance (yaw, that.getYaw ());
                var d = -that.ease (0, distance, speed);
                return Math.abs (d) > 0.01 ? d : null;
            }, function () {
                var d = that.ease (that.getPitch (), pitch, speed);
                return Math.abs (d) > 0.01 ? d : null;
            }, function () {
                var d = that.ease (that.getFov (), fov, speed);
                return Math.abs (d) > 0.01 ? d : null;
            }
        );
    },
    
    
    /**
     * Smoothly rotates the camera. If all of the dp, dy and df functions are null, stops
     * any smooth rotation.
     *
     * @param {function()} [dy] function giving the yaw increment for the next frame 
     * or null if no further yaw movement is required
     * @param {function()} [dp] function giving the pitch increment for the next frame 
     * or null if no further pitch movement is required
     * @param {function()} [df] function giving the field of view (degrees) increment 
     * for the next frame or null if no further fov adjustment is required
     */
    smoothRotate : function (dy, dp, df) {
        ++this.smoothrotatePermit;
        var savedPermit = this.smoothrotatePermit;
        if (!dp && !dy && !df) {
            return;
        }
        
        var that = this;
        var fs = {
            dy : dy,
            dp : dp,
            df : df,
            t : new Date ().getTime ()
        };
        var stepper = function () {
            if (that.smoothrotatePermit == savedPermit) {
                var now = new Date ().getTime ();
                var dat = now - fs.t;
                fs.t = now;
                
                var anyFunc = false;
                if (fs.dy) {
                    var d = fs.dy(dat);
                    if (d != null) {
                        anyFunc = true;
                        that.setYaw (that.getYaw () + d);
                    } else {
                        fs.dy = null;
                    }
                }
                
                if (fs.dp) {
                    var d = fs.dp(dat);
                    if (d != null) {
                        anyFunc = true;
                        that.setPitch (that.getPitch () + d);
                    } else {
                        fs.dp = null;
                    }
                }
                
                if (fs.df) {
                    var d = fs.df(dat);
                    if (d != null) {
                        anyFunc = true;
                        that.setFov (that.getFov () + d);
                    } else {
                        fs.df = null;
                    }
                }
                that.render ();
                if (anyFunc) {
                    that.browser.requestAnimationFrame (stepper, that.renderer.getElement ());
                }
            }
        };
        stepper ();
    },
    
    /**
     * Translates mouse wheel events.
     * @private
     */
    mouseWheel : function (event){
        var delta = 0;
        if (!event) /* For IE. */
            event = window.event;
        if (event.wheelDelta) { /* IE/Opera. */
            delta = event.wheelDelta / 120;
            /*
             * In Opera 9, delta differs in sign as compared to IE.
             */
            if (window.opera)
                delta = -delta;
        } else if (event.detail) { /* Mozilla case. */
            /*
             * In Mozilla, sign of delta is different than in IE.
             * Also, delta is multiple of 3.
             */
            delta = -event.detail;
        }
        
        /*
         * If delta is nonzero, handle it.
         * Basically, delta is now positive if wheel was scrolled up,
         * and negative, if wheel was scrolled down.
         */
        if (delta) {
            this.mouseWheelHandler (delta);
        }
        
        /*
         * Prevent default actions caused by mouse wheel.
         * That might be ugly, but we handle scrolls somehow
         * anyway, so don't bother here..
         */
        if (event.preventDefault) {
            event.preventDefault ();
        }
        event.returnValue = false;
    },
    
    /**
     * Utility function to interpret mouse wheel events.
     * @private
     */
    mouseWheelHandler : function (delta) {
        var that = this;
        var target = null;
        if (delta > 0) {
            if (this.getFov () > this.parameters.minFov) {
                target = this.getFov () * 0.9;
            }
        }
        if (delta < 0) {
            if (this.getFov () < this.parameters.maxFov) {
                target = this.getFov () / 0.9;
            }
        }
        if (target != null) {
            this.smoothRotate (null, null, function () {
                    var df = (target - that.getFov ()) / 1.5;
                    return Math.abs (df) > 0.01 ? df : null;
                });        
        }
    },
    
    /**
     * Maximizes the image to cover the browser viewport.
     * The container div is removed from its parent node upon entering 
     * full screen mode. When leaving full screen mode, the container
     * is appended to its old parent node. To avoid rearranging the
     * nodes, wrap the container in an extra div.
     *
     * <p>For unknown reasons (probably security), browsers will
     * not let you open a window that covers the entire screen.
     * Even when specifying "fullscreen=yes", all you get is a window
     * that has a title bar and only covers the desktop (not any task
     * bars or the like). For now, this is the best that I can do,
     * but should the situation change I'll update this to be
     * full-screen<i>-ier</i>.
     *
     * @param {function()} [onClose] function that is called when the user 
     * exits full-screen mode
     * @public
     */
    fullScreen : function (onClose) {
        if (this.fullScreenHandler) {
            return;
        }
        
        var message = document.createElement ("div");
        message.style.position = "absolute";
        message.style.fontSize = "16pt";
        message.style.top = "128px";
        message.style.width = "100%";
        message.style.color = "white";
        message.style.padding = "16px";
        message.style.zIndex = "9999";
        message.style.textAlign = "center";
        message.style.opacity = "0.75";
        message.innerHTML = "<span style='border-radius: 16px; -moz-border-radius: 16px; padding: 16px; padding-left: 32px; padding-right: 32px; background:black'>Press Esc to exit full screen mode.</span>";
        
        var that = this;
        
        this.fullScreenHandler = new bigshot.FullScreen (this.container);
        this.fullScreenHandler.restoreSize = this.sizeContainer == null;
        
        this.fullScreenHandler.addOnResize (function () {
                that.onresize ();
            });
        
        this.fullScreenHandler.addOnClose (function () {
                if (message.parentNode) {
                    try {
                        div.removeChild (message);
                    } catch (x) {
                    }
                }
                that.fullScreenHandler = null;
            });
        
        if (onClose) {
            this.fullScreenHandler.addOnClose (function () {
                    onClose ();
                });
        }
        
        this.removeEventListeners ();
        this.fullScreenHandler.open ();
        this.addEventListeners ();
        // Safari compatibility - must update after entering fullscreen.
        // 1s should be enough so we enter FS, but not enough for the
        // user to wonder if something is wrong.
        var r = function () {
            that.render ();
        };
        setTimeout (r, 1000);
        setTimeout (r, 2000);
        setTimeout (r, 3000);
        
        if (this.fullScreenHandler.getRootElement ()) {
            this.fullScreenHandler.getRootElement ().appendChild (message);
            
            setTimeout (function () {
                    var opacity = 0.75;
                    var iter = function () {
                        opacity -= 0.02;
                        if (message.parentNode) {
                            if (opacity <= 0) {
                                message.style.display = "none";
                                try {
                                    div.removeChild (message);
                                } catch (x) {}
                            } else {
                                message.style.opacity = opacity;
                                setTimeout (iter, 20);
                            }
                        }
                    };
                    setTimeout (iter, 20);
                }, 3500);
        }
        
        return function () {
            that.removeEventListeners ();
            that.fullScreenHandler.close ();
            that.addEventListeners ();
        };
    },
    
    /**
     * Right-sizes the canvas container.
     * @private
     */
    onresize : function () {
        if (this.fullScreenHandler == null || !this.fullScreenHandler.isFullScreen) {
            if (this.sizeContainer) {
                var s = this.browser.getElementSize (this.sizeContainer);
                this.renderer.resize (s.w, s.h);
            }
        } else {
            this.container.style.width = window.innerWidth + "px";
            this.container.style.height = window.innerHeight + "px";            
            var s = this.browser.getElementSize (this.container);
            this.renderer.resize (s.w, s.h);
        }
        this.renderer.onresize ();
        this.renderAsap ();            
    },
    
    /**
     * Posts a render() call via a timeout or the requestAnimationFrame API.
     * Use when the render call must be done as soon as possible, but 
     * can't be done in the current call context.
     */
    renderAsap : function () {
        if (!this.renderAsapPermitTaken && !this.disposed) {
            this.renderAsapPermitTaken = true;
            var that = this;
            this.browser.requestAnimationFrame (function () {
                    that.renderAsapPermitTaken = false;
                    that.render ();                    
                }, this.renderer.getElement ());
        }
    },
    
    
    /**
     * Automatically resizes the canvas element to the size of the 
     * given element on resize.
     *
     * @param {HTMLElement} sizeContainer the element to use. Set to <code>null</code>
     * to disable.
     */
    autoResizeContainer : function (sizeContainer) {
        this.sizeContainer = sizeContainer;
    }
}

/**
 * Fired when the user double-clicks on the panorama.
 *
 * @name bigshot.VRPanorama#dblclick
 * @event
 * @param {bigshot.VREvent} event the event object
 */

bigshot.Object.extend (bigshot.VRPanorama, bigshot.EventDispatcher);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Abstract base class for panorama hotspots.
 *
 * @class Abstract base class for panorama hotspots.
 *
 * A Hotspot is simply an HTML element that is moved / hidden etc.
 * to overlay a given position in the panorama.
 *
 * @param {bigshot.VRPanorama} panorama the panorama to attach this hotspot to
 */
bigshot.VRHotspot = function (panorama) {
    this.panorama = panorama;
    
    /**
     * The method to use for dealing with hotspots that extend outside the 
     * viewport. Note that {@link #CLIP_ADJUST} et al are functions, not constants.
     * To set the value, you must call the function to get a clipping strategy:
     *
     * @example
     * var hotspot = ...;
     * // note the function call below ---------------v
     * hotspot.clippingStrategy = hotspot.CLIP_ADJUST ();
     *
     * @see bigshot.VRHotspot#CLIP_ADJUST
     * @see bigshot.VRHotspot#CLIP_CENTER
     * @see bigshot.VRHotspot#CLIP_FRACTION
     * @see bigshot.VRHotspot#CLIP_ZOOM
     * @see bigshot.VRHotspot#CLIP_FADE
     * @see bigshot.VRHotspot#clip
     * @type function(clipData)
     * @default bigshot.VRHotspot#CLIP_ADJUST
     */
    this.clippingStrategy = bigshot.VRHotspot.CLIP_ADJUST (panorama);
    
}

/**
 * Hides the hotspot if less than <code>frac</code> of its area is visible.
 * 
 * @param {number} frac the fraction (0.0 - 1.0) of the hotspot that must be visible for
 * it to be shown.
 * @type function(clipData)
 * @see bigshot.VRHotspot#clip
 * @see bigshot.VRHotspot#clippingStrategy
 */
bigshot.VRHotspot.CLIP_FRACTION = function (panorama, frac) {
    return function (clipData) {
        var r = {
            x0 : Math.max (clipData.x, 0),
            y0 : Math.max (clipData.y, 0),
            x1 : Math.min (clipData.x + clipData.w, panorama.renderer.getViewportWidth ()),
            y1 : Math.min (clipData.y + clipData.h, panorama.renderer.getViewportHeight ())
        };
        var full = clipData.w * clipData.h;
        var visibleWidth = (r.x1 - r.x0);
        var visibleHeight = (r.y1 - r.y0);
        if (visibleWidth > 0 && visibleHeight > 0) {
            var visible = visibleWidth * visibleHeight;
            
            return (visible / full) >= frac;
        } else {
            return false;
        }
    }
};

/**
 * Hides the hotspot if its center is outside the viewport.
 * 
 * @type function(clipData)
 * @see bigshot.VRHotspot#clip
 * @see bigshot.VRHotspot#clippingStrategy
 */
bigshot.VRHotspot.CLIP_CENTER = function (panorama) {
    return function (clipData) {
        var c = {
            x : clipData.x + clipData.w / 2,
            y : clipData.y + clipData.h / 2
        };
        return c.x >= 0 && c.x < panorama.renderer.getViewportWidth () && 
        c.y >= 0 && c.y < panorama.renderer.getViewportHeight ();
    }
}

/**
 * Resizes the hotspot to fit in the viewport. Hides the hotspot if 
 * it is completely outside the viewport.
 * 
 * @type function(clipData)
 * @see bigshot.VRHotspot#clip
 * @see bigshot.VRHotspot#clippingStrategy
 */
bigshot.VRHotspot.CLIP_ADJUST = function (panorama) {
    return function (clipData) {
        if (clipData.x < 0) {
            clipData.w -= -clipData.x;
            clipData.x = 0;
        }
        if (clipData.y < 0) {
            clipData.h -= -clipData.y;
            clipData.y = 0;
        }
        if (clipData.x + clipData.w > panorama.renderer.getViewportWidth ()) {
            clipData.w = panorama.renderer.getViewportWidth () - clipData.x - 1;
        }
        if (clipData.y + clipData.h > panorama.renderer.getViewportHeight ()) {
            clipData.h = panorama.renderer.getViewportHeight () - clipData.y - 1;
        }
        
        return clipData.w > 0 && clipData.h > 0;
    }
}

/**
 * Shrinks the hotspot as it approaches the viewport edges.
 *
 * @param s The full size of the hotspot.
 * @param s.w The full width of the hotspot, in pixels.
 * @param s.h The full height of the hotspot, in pixels.
 * @see bigshot.VRHotspot#clip
 * @see bigshot.VRHotspot#clippingStrategy
 */
bigshot.VRHotspot.CLIP_ZOOM = function (panorama, s, maxDistanceInViewportHeights) {
    return function (clipData) {
        if (clipData.x >= 0 && clipData.y >= 0 && (clipData.x + s.w) < panorama.renderer.getViewportWidth ()
                && (clipData.y + s.h) < panorama.renderer.getViewportHeight ()) {
                    clipData.w = s.w;
                    clipData.h = s.h;
                    return true;
                }
        
        var distance = 0;
        if (clipData.x < 0) {
            distance = Math.max (-clipData.x, distance);
        }
        if (clipData.y < 0) {
            distance = Math.max (-clipData.y, distance);
        }
        if (clipData.x + s.w > panorama.renderer.getViewportWidth ()) {
            distance = Math.max (clipData.x + s.w - panorama.renderer.getViewportWidth (), distance);
        }
        if (clipData.y + s.h > panorama.renderer.getViewportHeight ()) {
            distance = Math.max (clipData.y + s.h - panorama.renderer.getViewportHeight (), distance);
        }
        
        distance /= panorama.renderer.getViewportHeight ();
        if (distance > maxDistanceInViewportHeights) {
            return false;
        }
        
        var scale = 1 / (1 + distance);
        
        clipData.w = s.w * scale;
        clipData.h = s.w * scale;
        if (clipData.x < 0) {
            clipData.x = 0;
        }
        if (clipData.y < 0) {
            clipData.y = 0;
        }
        if (clipData.x + clipData.w > panorama.renderer.getViewportWidth ()) {
            clipData.x = panorama.renderer.getViewportWidth () - clipData.w;
        }
        if (clipData.y + clipData.h > panorama.renderer.getViewportHeight ()) {
            clipData.y = panorama.renderer.getViewportHeight () - clipData.h;
        }
        
        return true;
    }
}

/**
 * Progressively fades the hotspot as it gets closer to the viewport edges.
 *
 * @param {number} borderSizeInPixels the distance from the edge, in pixels,
 * where the hotspot is completely opaque.
 * @see bigshot.VRHotspot#clip
 * @see bigshot.VRHotspot#clippingStrategy
 */
bigshot.VRHotspot.CLIP_FADE = function (panorama, borderSizeInPixels) {
    return function (clipData) {
        var distance = Math.min (
            clipData.x, 
            clipData.y, 
            panorama.renderer.getViewportWidth () - (clipData.x + clipData.w), 
            panorama.renderer.getViewportHeight () - (clipData.y + clipData.h));
        
        if (distance <= 0) {
            return false;
        } else if (distance <= borderSizeInPixels) {
            clipData.opacity = (distance / borderSizeInPixels);
            return true;
        } else {
            clipData.opacity = 1.0;
            return true;
        }
    }
}

bigshot.VRHotspot.prototype = {
    
    /**
     * Layout and resize the hotspot. Called by the panorama.
     */
    layout : function () {},
    
    /**
     * Helper function to rotate a point around an axis.
     *
     * @param {number} ang the angle
     * @param {bigshot.Point3D} vector the vector to rotate around
     * @param {Vector} point the point
     * @type Vector
     * @private
     */
    rotate : function (ang, vector, point) {
        var arad = ang * Math.PI / 180.0;
        var m = Matrix.Rotation(arad, $V([vector.x, vector.y, vector.z])).ensure4x4 ();
        return m.xPoint3Dhom1 (point);
    },
    
    /**
     * Converts the polar coordinates to world coordinates.
     * The distance is assumed to be 1.0.
     *
     * @param yaw the yaw, in degrees
     * @param pitch the pitch, in degrees
     * @type bigshot.Point3D
     */
    toVector : function (yaw, pitch) {
        var point = { x : 0, y : 0, z : -1 };
        point = this.rotate (-pitch, { x : 1, y : 0, z : 0 }, point);
        point = this.rotate (-yaw, { x : 0, y : 1, z : 0 }, point);
        return point;
    },
    
    /**
     * Converts the world-coordinate point p to screen coordinates.
     *
     * @param {bigshot.Point3D} p the world-coordinate point
     * @type point
     */
    toScreen : function (p) {
        var res = this.panorama.renderer.transformToScreen (p)
        return res;
    },
    
    /**
     * Clips the hotspot against the viewport. Both parameters 
     * are in/out. Clipping is done by adjusting the values of the
     * parameters.
     * 
     * @param clipData Information about the hotspot.
     * @param {number} clipData.x the x-coordinate of the top-left corner of the hotspot, in pixels.
     * @param {number} clipData.y the y-coordinate of the top-left corner of the hotspot, in pixels.
     * @param {number} clipData.w the width of the hotspot, in pixels.
     * @param {number} clipData.h the height of the hotspot, in pixels.
     * @param {number} [clipData.opacity] the opacity of the hotspot, ranging from 0.0 (transparent) 
     * to 1.0 (opaque). If set, the opacity of the hotspot element is adjusted.
     * @type boolean
     * @return true if the hotspot is visible, false otherwise
     */
    clip : function (clipData) {
        return this.clippingStrategy (clipData);
    }
}
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new point-hotspot and attaches it to a VR panorama.
 *
 * @class A VR panorama point-hotspot.
 *
 * A Hotspot is simply an HTML element that is moved / hidden etc.
 * to overlay a given position in the panorama. The element is moved
 * by setting its <code>style.top</code> and <code>style.left</code>
 * values.
 *
 * @augments bigshot.VRHotspot
 * @param {bigshot.VRPanorama} panorama the panorama to attach this hotspot to
 * @param {number} yaw the yaw coordinate of the hotspot
 * @param {number} pitch the pitch coordinate of the hotspot
 * @param {HTMLElement} element the HTML element
 * @param {number} offsetX the offset to add to the screen coordinate corresponding
 * to the hotspot's polar coordinates. Use this to center the hotspot horizontally.
 * @param {number} offsetY the offset to add to the screen coordinate corresponding
 * to the hotspot's polar coordinates. Use this to center the hotspot vertically.
 */
bigshot.VRPointHotspot = function (panorama, yaw, pitch, element, offsetX, offsetY) {
    bigshot.VRHotspot.call (this, panorama);
    this.element = element;
    this.offsetX = offsetX;
    this.offsetY = offsetY;
    this.point = this.toVector (yaw, pitch);
}
 
bigshot.VRPointHotspot.prototype = {
    layout : function () {
        var p = this.toScreen (this.point);
        
        var visible = false;
        if (p != null) {
            var s = this.panorama.browser.getElementSize (this.element);
            p.w = s.w;
            p.h = s.h;
            
            p.x += this.offsetX;
            p.y += this.offsetY;
            
            if (this.clip (p)) {
                this.element.style.top = (p.y) + "px";
                this.element.style.left = (p.x) + "px";
                this.element.style.width = (p.w) + "px";
                this.element.style.height = (p.h) + "px";
                if (p.opacity) {
                    this.element.style.opacity = p.opacity;
                }
                this.element.style.visibility = "inherit";
                visible = true;
            }
        }
        
        if (!visible) {
            this.element.style.visibility = "hidden";
        }
    }
}

bigshot.Object.extend (bigshot.VRPointHotspot, bigshot.VRHotspot);
bigshot.Object.validate ("bigshot.VRPointHotspot", bigshot.VRHotspot);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new rectangular hotspot and attaches it to a VR panorama.
 *
 * @class A rectangular VR panorama hotspot.
 *
 * A rectangular hotspot is simply an HTML element that is moved / resized / hidden etc.
 * to overlay a given rectangle in the panorama. The element is moved
 * by setting its <code>style.top</code> and <code>style.left</code>
 * values, and resized by setting its <code>style.width</code> and <code>style.height</code>
 * values.
 *
 * @augments bigshot.VRHotspot
 * @param {bigshot.VRPanorama} panorama the panorama to attach this hotspot to
 * @param {number} yaw0 the yaw coordinate of the top-left corner of the hotspot
 * @param {number} pitch0 the pitch coordinate of the top-left corner of the hotspot
 * @param {number} yaw1 the yaw coordinate of the bottom-right corner of the hotspot
 * @param {number} pitch1 the pitch coordinate of the bottom-right corner of the hotspot
 * @param {HTMLElement} element the HTML element
 */
bigshot.VRRectangleHotspot = function (panorama, yaw0, pitch0, yaw1, pitch1, element) {
    bigshot.VRHotspot.call (this, panorama);
    
    this.element = element;
    this.point0 = this.toVector (yaw0, pitch0);
    this.point1 = this.toVector (yaw1, pitch1);
}

bigshot.VRRectangleHotspot.prototype = {
    layout : function () {
        var p = this.toScreen (this.point0);
        var p1 = this.toScreen (this.point1);
        
        var visible = false;
        if (p != null && p1 != null) {
            var cd = {
                x : p.x,
                y : p.y,
                opacity : 1.0,
                w : p1.x - p.x,
                h : p1.y - p.y
            };
            
            if (this.clip (cd)) {
                this.element.style.top = (cd.y) + "px";
                this.element.style.left = (cd.x) + "px";
                this.element.style.width = (cd.w) + "px";
                this.element.style.height = (cd.h) + "px";
                this.element.style.visibility = "inherit";
                visible = true;
            }
        }
        
        if (!visible) {
            this.element.style.visibility = "hidden";
        }
    }
}

bigshot.Object.extend (bigshot.VRRectangleHotspot, bigshot.VRHotspot);
bigshot.Object.validate ("bigshot.VRRectangleHotspot", bigshot.VRHotspot);
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new parameter block.
 *
 * @class Parameters for the adaptive LOD monitor.
 */
bigshot.AdaptiveLODMonitorParameters = function (values) {
    
    /**
     * The VR panorama to adjust.
     *
     * @type bigshot.VRPanorama
     */
    this.vrPanorama = null;
    
    /**
     * The target framerate in frames per second. 
     * The monitor will try to achieve an average frame render time
     * of <i>1 / targetFps</i> seconds.
     *
     * @default 30
     * @type float
     */
    this.targetFps = 30;
    
    /**
     * The tolerance for the rendering time. The monitor will adjust the
     * level of detail if the average frame render time rises above
     * <i>target frame render time * (1.0 + tolerance)</i> or falls below
     * <i>target frame render time / (1.0 + tolerance)</i>.
     *
     * @default 0.3
     * @type float
     */
    this.tolerance = 0.3;
    
    /**
     * The rate at which the level of detail is adjusted.
     * For detail increase, the detail is multiplied with (1.0 + rate),
     * for decrease divided.
     *
     * @default 0.1
     * @type float
     */
    this.rate = 0.1;
    
    /**
     * Minimum texture magnification.
     *
     * @default 1.5
     * @type float
     */
    this.minMag = 1.5;
    
    /**
     * Maximum texture magnification.
     *
     * @default 16
     * @type float     
     */
    this.maxMag = 16;
    
    /**
     * Texture magnification for HQ render passes.
     *
     * @default 1.5
     * @type float     
     */
    this.hqRenderMag = 1.5;
    
    /**
     * Delay in milliseconds before executing 
     * a HQ render pass.
     *
     * @default 2000
     * @type int
     */
    this.hqRenderDelay = 2000;
    
    /**
     * Interval in milliseconds for the 
     * HQ render pass timer.
     *
     * @default 1000
     * @type int
     */
    this.hqRenderInterval = 1000;
    
    if (values) {
        for (var k in values) {
            this[k] = values[k];
        }
    }
    
    this.merge = function (values, overwrite) {
        for (var k in values) {
            if (overwrite || !this[k]) {
                this[k] = values[k];
            }
        }
    }
    return this;        
};
/*
 * Copyright 2010 - 2012 Leo Sutic <leo.sutic@gmail.com>
 *  
 * Licensed under the Apache License, Version 2.0 (the "License"); 
 * you may not use this file except in compliance with the License. 
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0 
 *     
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, 
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
 * See the License for the specific language governing permissions and 
 * limitations under the License. 
 */

/**
 * Creates a new adaptive level-of-detail monitor.
 *
 * @class An adaptive LOD monitor that adjusts the level of detail of a VR panorama
 * to achieve a desired frame rate. To connect it to a VR panorama, use the 
 * {@link bigshot.AdaptiveLODMonitor#getListener} method to get a render listener 
 * that can be passed to {@link bigshot.VRPanorama#addRenderListener}.
 *
 * <p>The monitor maintains two render modes - a high quality one with a fixed
 * level of detail, and a low(er) quality one with variable level of detail.
 * If the panorama is idle for more than a set interval, a high-quality render is
 * performed.
 * 
 * @param {bigshot.AdaptiveLODMonitorParameters} parameters parameters for the LOD monitor.
 *
 * @see bigshot.AdaptiveLODMonitorParameters for a list of parameters
 *
 * @example
 * var bvr = new bigshot.VRPanorama ( ... );
 * var lodMonitor = new bigshot.AdaptiveLODMonitor (
 *     new bigshot.AdaptiveLODMonitorParameters ({
 *         vrPanorama : bvr,
 *         targetFps : 30,
 *         tolerance : 0.3,
 *         rate : 0.1,
 *         minMag : 1.5,
 *         maxMag : 16
 *     }));
 * bvr.addRenderListener (lodMonitor.getListener ());
 */
bigshot.AdaptiveLODMonitor = function (parameters) {
    this.setParameters (parameters);
    
    /**
     * The current adaptive detail level.
     * @type float
     * @private
     */
    this.currentAdaptiveMagnification = parameters.vrPanorama.getMaxTextureMagnification ();
    
    /**
     * The number of frames that have been rendered.
     * @type int
     * @private
     */
    this.frames = 0;
    
    /**
     * The total number of times we have sampled the render time.
     * @type int
     * @private
     */
    this.samples = 0;
    
    /**
     * The sum of sample times from all samples of render time in milliseconds.
     * @type int
     * @private
     */
    this.renderTimeTotal = 0;
    
    /**
     * The sum of sample times from the recent sample pass in milliseconds.
     * @type int
     * @private
     */
    this.renderTimeLast = 0;
    
    /**
     * The number of samples currently done in the recent sample pass.
     * @type int
     * @private
     */
    this.samplesLast = 0;
    
    /**
     * The start time, in milliseconds, of the last sample.
     * @type int
     * @private
     */
    this.startTime = 0;
    
    /**
     * The time, in milliseconds, when the panorama was last rendered.
     * @type int
     * @private
     */
    this.lastRender = 0;
    
    this.hqRender = false;
    this.hqMode = false;
    this.hqRenderWaiting = false;
    
    /**
     * Flag to enable / disable the monitor.
     * @type boolean
     * @private
     */
    this.enabled = true;
    
    var that = this;
    this.listenerFunction = function (state, cause, data) {
        that.listener (state, cause, data);
    };         
};

bigshot.AdaptiveLODMonitor.prototype = {
    averageRenderTime : function () {
        if (this.samples > 0) {
            return this.renderTimeTotal / this.samples;
        } else {
            return -1;
        }
    },
    
    /**
     * @param {bigshot.AdaptiveLODMonitorParameters} parameters
     */
    setParameters : function (parameters) {
        this.parameters = parameters;
        this.targetTime = 1000 / this.parameters.targetFps;
        
        this.lowerTime = this.targetTime / (1.0 + this.parameters.tolerance);
        this.upperTime = this.targetTime * (1.0 + this.parameters.tolerance);
    },
    
    setEnabled : function (enabled) {
        this.enabled = enabled;
    },
    
    averageRenderTimeLast : function () {
        if (this.samples > 0) {
            return this.renderTimeLast / this.samplesLast;
        } else {
            return -1;
        }
    },
    
    getListener : function () {
        return this.listenerFunction;
    },
    
    increaseDetail : function () {
        this.currentAdaptiveMagnification = Math.max (this.parameters.minMag, this.currentAdaptiveMagnification / (1.0 + this.parameters.rate));
    },
    
    decreaseDetail : function () {
        this.currentAdaptiveMagnification = Math.min (this.parameters.maxMag, this.currentAdaptiveMagnification * (1.0 + this.parameters.rate));
    },
    
    sample : function () {
        var deltat = new Date ().getTime () - this.startTime;
        this.samples++;
        this.renderTimeTotal += deltat;
        
        this.samplesLast++;
        this.renderTimeLast += deltat;
        
        if (this.samplesLast > 4) {
            var averageLast = this.renderTimeLast / this.samplesLast;                        
            
            if (averageLast < this.lowerTime) {
                this.increaseDetail ();
            } else if (averageLast > this.upperTime) {
                this.decreaseDetail ();
            }
            
            this.samplesLast = 0;
            this.renderTimeLast = 0;
        }
    },
    
    hqRenderTick : function () {
        if (this.lastRender < new Date ().getTime () - this.parameters.hqRenderDelay) {
            this.hqRender = true;
            this.hqMode = true;
            if (this.enabled) {
                this.parameters.vrPanorama.setMaxTextureMagnification (this.parameters.hqRenderMag);
                this.parameters.vrPanorama.render ();
            }
            
            this.hqRender = false;
            this.hqRenderWaiting = false;
        } else {
            var that = this;
            setTimeout (function () {
                    that.hqRenderTick ();
                }, this.parameters.hqRenderInterval);
        }
    },
    
    listener : function (state, cause, data) {
        if (!this.enabled) {
            return;
        }
        
        if (this.hqRender) {
            return;
        }
        
        if (this.hqMode && cause == bigshot.VRPanorama.ONRENDER_TEXTURE_UPDATE) {
            this.parameters.vrPanorama.setMaxTextureMagnification (this.parameters.minMag);
            return;
        } else {
            this.hqMode = false;
        }
        
        this.parameters.vrPanorama.setMaxTextureMagnification (this.currentAdaptiveMagnification);
        
        this.frames++;
        if ((this.frames < 20 || this.frames % 5 == 0) && state == bigshot.VRPanorama.ONRENDER_BEGIN) {
            this.startTime = new Date ().getTime ();
            this.lastRender = this.startTime;
            var that = this;
            setTimeout (function () {
                    that.sample ();
                }, 1);
            if (!this.hqRenderWaiting) {
                this.hqRenderWaiting = true;
                setTimeout (function () {
                        that.hqRenderTick ();
                    }, this.parameters.hqRenderInterval);
            }
        }
    }
};
}
