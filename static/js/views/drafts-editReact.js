(() => {
  function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }
  "use strict";

  const {
    useCallback,
    useEffect,
    useMemo,
    useState
  } = React;
  const {
    Text,
    Range,
    Editor,
    Transforms,
    createEditor,
    Node
  } = Slate;
  const {
    Editable,
    withReact,
    useSlate
  } = SlateReact;
  const {
    withHistory
  } = SlateHistory;
  const {
    jsx
  } = SlateHyperscript;
  const SlateEl = SlateReact.Slate; // Define our own custom set of helpers.

  const isHot = isHotkey.isHotkey;
  const Tool = {
    marks: ["bold", "italic"],
    blocks: ["seperator", "paragraph", "center"],
    match_mark: {
      bold: (tagName, style) => ['b', 'strong'].includes(tagName) || parseInt(style.fontWeight) >= 550,
      italic: (tagName, style) => ['i', 'em'].includes(tagName) || style.fontStyle === "italic"
    },
    mark_tags: {
      EM: () => ({
        italic: true
      }),
      I: () => ({
        italic: true
      }),
      STRONG: () => ({
        bold: true
      }),
      B: () => ({
        bold: true
      })
    },
    block_tags: {
      P: () => ({
        type: 'paragraph'
      }),
      HR: () => ({
        type: 'seperator'
      })
    }
  };
  Tool.hotkeys = {
    'mod+b': 'bold',
    'mod+i': 'italic',
    'mod+e`': 'center',
    'mod+h`': 'seperator'
  }; // Check

  Tool.isBlockActive = (editor, format) => {
    if (format === "seperator") {
      return false;
    }

    const [match] = Editor.nodes(editor, {
      match: n => n.type === format
    });
    return !!match;
  };

  Tool.isMarkActive = (editor, format) => {
    const marks = Editor.marks(editor);
    return marks ? marks[format] === true : false;
  };

  Tool.isActive = (editor, tool) => {
    return Tool.blocks.includes(tool) ? Tool.isBlockActive(editor, tool) : Tool.isMarkActive(editor, tool);
  }; // Toggle


  Tool.toggleBlock = (editor, format) => {
    if (format === "seperator") {
      Transforms.insertNodes(editor, [{
        type: "seperator",
        children: [{
          text: ""
        }]
      }, {
        type: "paragraph",
        children: [{
          text: ""
        }]
      }]);
      return;
    }

    const isActive = Tool.isBlockActive(editor, format); // Set to Format or back to paragraph

    Transforms.setNodes(editor, {
      type: isActive ? 'paragraph' : format
    });
  };

  Tool.toggleMark = (editor, format) => {
    const isActive = Tool.isMarkActive(editor, format);

    if (isActive) {
      Editor.removeMark(editor, format);
    } else {
      Editor.addMark(editor, format, true);
    }
  };

  Tool.toggle = (editor, tool) => {
    return Tool.blocks.includes(tool) ? Tool.toggleBlock(editor, tool) : Tool.toggleMark(editor, tool);
  };

  let autoSaveOpt = 0;

  const shouldAutosave = editor => {
    const opr = editor.operations;
    const actAt = 50;

    if (opr.length === 1 && opr[0].type === 'set_selection') {
      return false;
    }

    if (autoSaveOpt >= actAt) {
      autoSaveOpt = 0;
      return true;
    }

    let largestLength = 0;

    for (let j = 0; j < opr.length; j++) {
      const singleOpr = opr[j];
      const thisLength = singleOpr.text ? singleOpr.text.length : singleOpr.node && singleOpr.node.text ? singleOpr.node.text.length : 0;
      largestLength = thisLength > largestLength ? thisLength : largestLength;

      if (autoSaveOpt + thisLength >= actAt) {
        autoSaveOpt = 0;
        return true;
      }
    }

    autoSaveOpt += largestLength;
    return false;
  };

  window.editedBlocks = [];

  const App = () => {
    const editor = useMemo(() => withHistory(withReact(createEditor())), []);
    window.DraftEditor = editor;

    if (!window.insertEditorText) {
      window.insertEditorText = text => {
        Editor.insertText(editor, text);
      };
    }

    const [value, setValue] = useState([{
      type: 'paragraph',
      children: [{
        text: ''
      }]
    }]); // Define a rendering function based on the element passed to `props`. We use
    // `useCallback` here to memoize the function for subsequent renders.

    window.draftContent = {};
    window.draftContent.value = value;
    window.draftContent.set = setValue;
    const renderElement = useCallback(props => /*#__PURE__*/React.createElement(Block, props), []);
    const renderLeaf = useCallback(props => /*#__PURE__*/React.createElement(Leaf, props), []);
    editor.isVoid = useCallback(element => element.type === "seperator");
    const prevDeleteFragment = useCallback(editor.deleteFragment.bind(editor));
    editor.deleteFragment = useCallback(() => {
      const {
        selection
      } = editor;

      if (selection && Range.isExpanded(selection)) {
        const seperators = Array.from(Editor.nodes(editor, {
          match: n => n.type === "seperator"
        }));

        if (!!seperators.length) {
          // We're only deleting the last image as that's what is always left behind.
          // Slate is handling the rest easily.
          const [, cellPath] = seperators[seperators.length - 1];
          Transforms.delete(editor, {
            at: cellPath,
            voids: true
          });
        }
      }

      prevDeleteFragment();
    }); // Paste HTML

    const prevInsertData = useCallback(editor.insertData.bind(editor));
    editor.insertData = useCallback(data => {
      const html = data.getData('text/html');

      if (html) {
        const parsed = new DOMParser().parseFromString(html, 'text/html');
        let doc = parsed.body;
        const GDocsInternal = doc.querySelector('[id^="docs-internal"]');

        if (GDocsInternal) {
          doc = GDocsInternal;
        }

        let fragment = deserialize(parsed.body).filter(child => child !== null);

        while (fragment[0].text && fragment[0].text.trim() === "") {
          fragment[0].text = "";
        }

        Transforms.insertFragment(editor, fragment);
        return;
      }

      prevInsertData(data);
    });
    return /*#__PURE__*/React.createElement(SlateEl, {
      editor: editor,
      value: value,
      autoFocus: true,
      onChange: value => {
        const opr = editor.operations;

        if (!(opr.length === 1 && opr[0].type === 'set_selection')) {
          opr.forEach(operation => {
            (operation.path || []).forEach(pa => {
              window.editedBlocks.push(pa + Math.max(0, window.draftLastLength - value.length));
              window.editedBlocks.push(pa);
            });
          });
          window.editedAtAll = true;
          window.autosaveDraft(value, shouldAutosave(editor));
        }

        setValue(value);
      }
    }, /*#__PURE__*/React.createElement("toolbar", null, /*#__PURE__*/React.createElement(ToolButton, {
      action: "bold"
    }), /*#__PURE__*/React.createElement(ToolButton, {
      action: "italic"
    }), /*#__PURE__*/React.createElement(ToolButton, {
      action: "center"
    }), /*#__PURE__*/React.createElement(ToolButton, {
      action: "seperator"
    })), /*#__PURE__*/React.createElement(Editable, {
      renderElement: renderElement,
      renderLeaf: renderLeaf,
      onKeyDown: event => {
        for (const hotkey in Tool.hotkeys) {
          if (isHot(hotkey, event)) {
            event.preventDefault();
            Tool.toggle(editor, Tool.hotkeys[hotkey]);
          }
        }
      }
    }));
  };

  const Block = ({
    attributes,
    children,
    element
  }) => {
    switch (element.type) {
      case 'center':
        attributes.style = {
          textAlign: "center"
        };
        return /*#__PURE__*/React.createElement("p", attributes, children);

      case 'seperator':
        attributes.className = "hr";
        return /*#__PURE__*/React.createElement("div", attributes, children);

      default:
        return /*#__PURE__*/React.createElement("p", attributes, children);
    }
  };

  const Leaf = ({
    attributes,
    children,
    leaf
  }) => {
    return /*#__PURE__*/React.createElement("span", _extends({}, attributes, {
      style: {
        fontWeight: leaf.bold ? 'bold' : 'normal',
        fontStyle: leaf.italic ? 'italic' : 'normal'
      }
    }), children);
  };

  const ToolButton = ({
    action
  }) => {
    const editor = useSlate();
    const activeClassName = Tool.isActive(editor, action) ? "active" : "";
    return /*#__PURE__*/React.createElement("button", {
      action: action,
      className: activeClassName,
      onMouseDown: event => {
        event.preventDefault();
        Tool.toggle(editor, action);
      }
    });
  };

  const deserialize = el => {
    if (el.nodeType === 3) {
      return {
        text: el.textContent
      };
    } else if (el.nodeType !== 1) {
      return null;
    }

    const tagName = el.tagName.toLowerCase();
    const children = Array.from(el.childNodes).map(deserialize).flat();

    if (tagName === "p") {
      const align = el.style.textAlign;
      return {
        children,
        type: align === "center" ? "center" : 'paragraph'
      };
    }

    if (tagName == "hr") {
      return {
        children: [],
        type: "seperator"
      };
    }

    if (['span', 'strong', 'em', 'i'].includes(tagName)) {
      const attrs = {};

      for (const attr in Tool.match_mark) {
        if (Tool.match_mark[attr](tagName, el.style)) {
          attrs[attr] = true;
        }
      }

      return children.filter(child => Text.isText(child)).map(child => jsx("text", attrs, child));
    }

    return children;
  };

  ReactDOM.render( /*#__PURE__*/React.createElement(App, null), DOM.q('editor'));
})();