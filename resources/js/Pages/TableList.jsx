import React, { useState } from "react";
import { DATA_TYPES_TEXT_MAP } from "@/constants.jsx";
import TextInput from "@/Components/TextInput";
import { router } from "@inertiajs/react";

export default function TableList({ tables, tableParams, onInsert }) {
    const [expandedTable, setExpandedTable] = useState(null);
    const [searchKeyword, setSearchKeyword] = useState("");

    const searchTableChanged = (name, value) => {
        if (value) {
            tableParams[name] = value;
        } else {
            delete tableParams[name];
        }

        router.get(route("query.index"), tableParams);
    };

    const onKeyPress = (name, e) => {
        if (e.key !== "Enter") return;

        searchTableChanged(name, e.target.value);
    };

    const toggleTable = (tableName) => {
        setExpandedTable(expandedTable === tableName ? null : tableName);
    };

    const filteredTables = Object.entries(tables).filter(([tableName]) =>
        tableName.toLowerCase().includes(searchKeyword.toLowerCase())
    );

    return (
        <div className="container mx-auto w-full h-full mt-2">
            <div className="flex flex-col h-full items-center">
                <TextInput
                    className="w-full mb-2"
                    value={searchKeyword}
                    placeholder="Search Table"
                    onChange={(e) => setSearchKeyword(e.target.value)}
                    onBlur={(e) => searchTableChanged("name", e.target.value)}
                    onKeyPress={(e) => onKeyPress("name", e)}
                />

                <ul className="list-none bg-gray-700 w-full h-full overflow-auto">
                    {filteredTables.map(([tableName, fields]) => (
                        <li key={tableName} className="mb-2 ml-2">
                            <div
                                className="flex items-center justify-between cursor-pointer"
                                onClick={() => toggleTable(tableName)}
                            >
                                <div className="flex items-center">
                                    <span className="mr-2 text-2xl dark:hover:text-gray-100">
                                        {expandedTable === tableName ? (
                                            <i className="fas fa-minus"></i>
                                        ) : (
                                            <i className="fas fa-plus"></i>
                                        )}
                                    </span>
                                    <span className="font-semibold text-lg text-gray-200">
                                        {tableName}
                                    </span>
                                </div>
                                <i
                                    className="fa-solid fa-arrow-right value-insert mr-2"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        onInsert(tableName);
                                    }}
                                ></i>
                            </div>
                            {expandedTable === tableName && (
                                <ul className="ml-6 list-disc">
                                    {fields.map((field) => (
                                        <li
                                            key={field.name}
                                            className="flex items-center justify-between"
                                        >
                                            <div className="flex items-center">
                                                <p className="font-semibold text-base mr-2 text-gray-200">
                                                    {field.name}
                                                </p>
                                                <label className="text-gray-500">
                                                    {DATA_TYPES_TEXT_MAP[
                                                        field.type
                                                    ] || field.type}
                                                </label>
                                            </div>
                                            <i
                                                className="fa-solid fa-arrow-right ml-auto mr-2 dark:hover:color-gray-100"
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    onInsert(field.name);
                                                }}
                                            ></i>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </li>
                    ))}
                </ul>
            </div>
        </div>
    );
}
