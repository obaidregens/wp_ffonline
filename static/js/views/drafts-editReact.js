"use strict";

function _extends() { _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; }; return _extends.apply(this, arguments); }

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
  createEditor
} = Slate;
const {
  Editable,
  withReact
} = SlateReact;
const {
  withHistory
} = SlateHistory;
var {
  Slate
} = SlateReact; // Define our own custom set of helpers.
const EditorTools = {
  italic: {
    isActive(editor) {
      const [match] = Editor.nodes(editor, {
        match: n => n.italic === true,
        universal: true
      });
      return !!match;
    },

    toggle(editor) {
      const isActive = EditorTools.italic.isActive(editor);
      Transforms.setNodes(editor, {
        italic: isActive ? null : true
      }, {
        match: n => Text.isText(n),
        split: true
      });
    }
  },
  bold: {
    isActive(editor) {
      const [match] = Editor.nodes(editor, {
        match: n => n.bold === true,
        universal: true
      });
      return !!match;
    },

    toggle(editor) {
      const isActive = EditorTools.bold.isActive(editor);
      Transforms.setNodes(editor, {
        bold: isActive ? null : true
      }, {
        match: n => Text.isText(n),
        split: true
      });
    }

  },
  center: {
    isActive(editor) {
      const [match] = Editor.nodes(editor, {
        match: n => n.type === 'center'
      });
      return !!match;
    },
    toggle(editor) {
      const isActive = EditorTools.center.isActive(editor);
      Transforms.setNodes(editor, {
        type: isActive ? null : 'center'
      }, {
        match: n => Editor.isBlock(editor, n)
      });
    }

  }
};
let autoSaveOpt = 0;
const shouldAutosave = (editor) => {
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
    const thisLength = singleOpr.text ? singleOpr.text.length : ( (singleOpr.node && singleOpr.node.text) ? singleOpr.node.text.length : 0);
    largestLength = thisLength > largestLength ? thisLength : largestLength;
    if ((autoSaveOpt + thisLength) >= actAt) {
      autoSaveOpt = 0;
      return true;
    }
  }
  autoSaveOpt += largestLength;
  return false;
} 
let insertEditorText;
const App = () => {
  const editor = useMemo(() => withHistory(withReact(createEditor())), []); // Add the initial value when setting up our state.
  window.DraftEditor = editor;
  if (! insertEditorText) {
    insertEditorText = (text) => {
      Editor.insertText(editor,text);
    }
  }
  const [value, setValue] = useState([
    {
      type: 'paragraph',
      children: [{ text: '' }],
    },
  ]); // Define a rendering function based on the element passed to `props`. We use
  // `useCallback` here to memoize the function for subsequent renders.
  window.draftContent = {}
  window.draftContent.value = value;
  window.draftContent.set = setValue;
  
  const renderElement = useCallback(props => {
    switch (props.element.type) {
      case 'center':
        return /*#__PURE__*/React.createElement(CenterElement, props);

      default:
        return /*#__PURE__*/React.createElement(ParagraphElement, props);
    }
  }, []);
  const renderLeaf = useCallback(props => {
    return /*#__PURE__*/React.createElement(Leaf, props);
  }, []);
  return /*#__PURE__*/React.createElement(Slate, {
    editor: editor,
    value: value,
    onChange: value => {
      const opr = editor.operations;
      if (!(opr.length === 1 && opr[0].type === 'set_selection')) {
        window.editedAtAll = true;
        window.autosaveDraft(value,shouldAutosave(editor));
      }
      setValue(value);
    }
  }, /*#__PURE__*/React.createElement("toolbar", null, /*#__PURE__*/React.createElement("button", {
    onClick: EditorTools.bold.toggle.bind(null, editor),
    action: "bold"
  }), /*#__PURE__*/React.createElement("button", {
    onClick: EditorTools.italic.toggle.bind(null, editor),
    action: "italic"
  }), /*#__PURE__*/React.createElement("button", {
    onClick: EditorTools.center.toggle.bind(null, editor),
    action: "center"
  })), /*#__PURE__*/React.createElement(Editable, {
    renderElement: renderElement,
    renderLeaf: renderLeaf,
    onKeyDown: event => {
      if (!_.cmd(event)) {
        return;
      }

      if (['e', 'E'].includes(event.key)) {
        event.preventDefault();
        EditorTools.center.toggle(editor);
      } else if (['b', 'B'].includes(event.key)) {
        event.preventDefault();
        EditorTools.bold.toggle(editor);
      } else if (['i', 'I'].includes(event.key)) {
        event.preventDefault();
        EditorTools.italic.toggle(editor);
      }
    }
  }));
};

const ParagraphElement = props => {
  return /*#__PURE__*/React.createElement("p", props.attributes, props.children);
};

const CenterElement = props => {
  props.attributes.style = {
    textAlign: 'center'
  };
  return /*#__PURE__*/React.createElement("p", props.attributes, props.children);
};

const Leaf = props => {
  return /*#__PURE__*/React.createElement("span", _extends({}, props.attributes, {
    style: {
      fontWeight: props.leaf.bold ? 'bold' : 'normal',
      fontStyle: props.leaf.italic ? 'italic' : 'normal'
    }
  }), props.children);
};

ReactDOM.render( /*#__PURE__*/React.createElement(App, null), DOM.q('editor'));