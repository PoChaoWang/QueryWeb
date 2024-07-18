import Pagination from "@/Components/Pagination";
import React, { useState } from "react";
import CodeArea from "./CodeArea";

export default function RecordingTable({ recordings }) {
    const [visibleIds, setVisibleIds] = useState(new Set());
    const [recordingList, setRecordingList] = useState(recordings.data);

    const handleButtonClick = (id) => {
        setVisibleIds((prevVisibleIds) => {
            const newVisibleIds = new Set(prevVisibleIds);
            if (newVisibleIds.has(id)) {
                newVisibleIds.delete(id);
            } else {
                newVisibleIds.add(id);
            }
            return newVisibleIds;
        });
    };

    const downloadCSV = (filePath) => {
        window.location.href = filePath;
    };

    return (
        <>
            {recordingList.map((recording) => (
                <div className="py-2" key={recording.id}>
                    <div className="max-w-7xl mx-auto">
                        <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-3 text-gray-900 dark:text-gray-100">
                                <div className="flex justify-between items-center">
                                    <div>
                                        <label className="px-3 py-3 mr-2">
                                            Ran By: {recording.updated_by.name}
                                        </label>
                                        <label className="px-3 py-3 mr-2">
                                            Ran At: {recording.updated_at}
                                        </label>
                                    </div>

                                    <div>
                                        <button
                                            onClick={() =>
                                                handleButtonClick(recording.id)
                                            }
                                            className="bg-gray-700 py-1 px-3 text-xl text-white rounded shadow transition-all hover:bg-gray-900 mr-3"
                                        >
                                            {visibleIds.has(recording.id)
                                                ? "Hide Code"
                                                : "Show Code"}
                                        </button>

                                        <button
                                            onClick={() =>
                                                downloadCSV(
                                                    recording.csv_file_path
                                                )
                                            }
                                            className="bg-gray-700 py-1 px-3 text-xl text-white rounded shadow transition-all hover:bg-green-500 mr-3"
                                        >
                                            Download
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label className="px-3 py-3 mr-2">
                                        Status: {recording.status}
                                    </label>
                                    {recording.status === "fail" && (
                                        <label className="px-3 py-3 mr-2 text-red-500">
                                            Reason: {recording.fail_reason}
                                        </label>
                                    )}
                                </div>

                                {visibleIds.has(recording.id) && (
                                    <div className="mt-3">
                                        <CodeArea
                                            value={recording.query_sql}
                                            readOnly={true}
                                        />
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            ))}
            <Pagination links={recordings.meta.links} />
        </>
    );
}
