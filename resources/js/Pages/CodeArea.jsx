import React, { useEffect } from "react";
import AceEditor from "react-ace";
import "ace-builds/src-noconflict/mode-sql";
import "ace-builds/src-noconflict/theme-monokai";
import "ace-builds/src-noconflict/ext-language_tools";

const CodeArea = ({ value, onChange, editorRef, readOnly = false }) => {
    return (
        <AceEditor
            mode="sql"
            theme="monokai"
            name="query_sql"
            fontSize={14}
            width="100%"
            value={value}
            onChange={onChange}
            setOptions={{
                enableBasicAutocompletion: true,
                enableLiveAutocompletion: true,
                showLineNumbers: true,
                tabSize: 2,
                useWorker: false,
                printMargin: false,
                readOnly: readOnly, // 设置只读模式
            }}
            ref={editorRef}
        />
    );
};

export default CodeArea;
