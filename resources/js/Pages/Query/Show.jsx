import React, { useState } from "react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, router, useForm } from "@inertiajs/react";
import axios from "axios";
import RecordingTable from "../RecordingTable";
import ScheduleTable from "../ScheduleTable";
import OutputtingTable from "../OutputtingTable";

export default function Show({
    auth,
    currentDatabase,
    query,
    recordings,
    outputtings,
    schedules,
    success,
    fail,
}) {
    const [activeTab, setActiveTab] = useState("Recording");

    const deleteQuery = (query) => {
        if (!window.confirm("Are you sure you want to delete the query?")) {
            return;
        }
        router.delete(route("query.destroy", query.id));
    };

    const runQuery = async (queryId) => {
        try {
            const response = await axios.post(`/recordings/execute/${queryId}`);
            if (response.data.success) {
                window.location.reload();
            } else {
                console.error("Error running query:", response.data.message);
            }
        } catch (error) {
            console.error("Error running query:", error);
            console.error("Error response:", error.response.data);
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        {currentDatabase} - {query.name}
                    </h2>
                    <div>
                        <button
                            onClick={() => runQuery(query.id)}
                            className="bg-green-500 py-1 px-3 text-white rounded shadow transition-all hover:bg-green-700 mr-3"
                        >
                            Run
                        </button>
                        <Link
                            href={route("query.edit", {
                                client: currentDatabase,
                                id: query.id,
                            })}
                            className="bg-blue-500 py-1 px-3 text-white rounded shadow transition-all hover:bg-blue-900 mr-3"
                        >
                            Edit
                        </Link>
                        <button
                            onClick={() => deleteQuery(query)}
                            className="bg-red-500 py-1 px-3 text-white rounded shadow transition-all hover:bg-red-900"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            }
        >
            <Head title={query.name} />
            <div className="pt-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col">
                    {success && (
                        <div className="bg-emerald-500 py-2 px-4 text-white rounded mb-4">
                            {success}
                        </div>
                    )}
                    {fail && (
                        <div className="bg-red-500 py-2 px-4 text-white rounded mb-4">
                            {fail}
                        </div>
                    )}
                    <div className="flex mt-2">
                        <button
                            onClick={() => setActiveTab("Recording")}
                            className={`font-semibold text-center text-xl text-gray-800 dark:text-gray-200 leading-tight w-1/6 dark:bg-gray-800 dark:hover:bg-gray-500 hover:bg-gray-400 overflow-hidden shadow-sm rounded-tr-3xl px-3 py-3 border-b-2 border-gray-500 ${
                                activeTab === "Recording"
                                    ? "bg-gray-300 dark:bg-gray-400"
                                    : ""
                            }`}
                        >
                            Recording
                        </button>
                        <button
                            onClick={() => setActiveTab("Schedule")}
                            className={`font-semibold text-center text-xl text-gray-800 dark:text-gray-200 leading-tight w-1/6 dark:bg-gray-800 dark:hover:bg-gray-500 hover:bg-gray-400 overflow-hidden shadow-sm rounded-tr-3xl px-3 py-3 border-b-2 border-gray-500 ${
                                activeTab === "Schedule"
                                    ? "bg-gray-300 dark:bg-gray-400"
                                    : ""
                            }`}
                        >
                            Schedule
                        </button>
                        <button
                            onClick={() => setActiveTab("Output")}
                            className={`font-semibold text-center text-xl text-gray-800 dark:text-gray-200 leading-tight w-1/6 dark:bg-gray-800 dark:hover:bg-gray-500 hover:bg-gray-400 overflow-hidden shadow-sm rounded-tr-3xl px-3 py-3 border-b-2 border-gray-500 ${
                                activeTab === "Output"
                                    ? "bg-gray-300 dark:bg-gray-400"
                                    : ""
                            }`}
                        >
                            Output
                        </button>
                    </div>
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-br-lg rounded-bl-lg">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <div className="grid gap-1 grid-cols-3 mt-2">
                                <div>
                                    <div>
                                        <label className="font-bold text-lg">
                                            Created By
                                        </label>
                                        <p className="mt-1">
                                            {query.created_by.name}
                                        </p>
                                    </div>
                                    <div className="mt-4">
                                        <label className="font-bold text-lg">
                                            Updated By
                                        </label>
                                        <p className="mt-1">
                                            {query.updated_by.name}
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    <div>
                                        <label className="font-bold text-lg">
                                            Created At
                                        </label>
                                        <p className="mt-1">
                                            {query.created_at}
                                        </p>
                                    </div>
                                    <div className="mt-4">
                                        <label className="font-bold text-lg">
                                            Updated At
                                        </label>
                                        <p className="mt-1">
                                            {query.updated_at}
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    <div>
                                        <label className="font-bold text-lg">
                                            Client
                                        </label>
                                        <p className="mt-1">
                                            {currentDatabase}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="mt-4">
                        {activeTab === "Recording" && (
                            <RecordingTable
                                recordings={recordings}
                                queryId={query.id}
                            />
                        )}
                        {activeTab === "Schedule" && (
                            <ScheduleTable schedules={schedules} />
                        )}
                        {activeTab === "Output" && (
                            <OutputtingTable
                                outputtings={outputtings}
                                queryId={query.id}
                            />
                        )}
                    </div>
                </div>
            </div>
            {/* <pre className="text-white">{JSON.stringify(query, null, 2)}</pre> */}
        </AuthenticatedLayout>
    );
}
