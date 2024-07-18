import React, { useState } from "react";
import TextInput from "@/Components/TextInput";
import axios from "axios";
import { router, useForm } from "@inertiajs/react";

export default function OutputtingTable({ outputtings, queryId }) {
    const [isEditing, setIsEditing] = useState(null);
    const [tempData, setTempData] = useState({
        sheetId: "",
        sheetName: "",
        append: false,
    });

    const { data, setData, post, reset } = useForm({
        sheetId: "",
        sheetName: "",
        append: false,
    });

    const handleSaveNew = async () => {
        try {
            await post(route("outputting.store", { query: queryId }), {
                onSuccess: () => {
                    router.reload({ only: ["outputtings"] });
                    reset();
                },
            });
        } catch (error) {
            console.error("Error saving new outputting:", error);
        }
    };

    const handleEdit = (outputting) => {
        setIsEditing(outputting.id);
        setTempData({
            sheetId: outputting.sheet_id,
            sheetName: outputting.sheet_name,
            append: outputting.append,
        });
    };

    const handleSaveEdit = async (id) => {
        try {
            await axios.put(
                route("outputting.update", { query: queryId, outputting: id }),
                tempData
            );
            setIsEditing(null);
            setTempData({
                sheetId: "",
                sheetName: "",
                append: false,
            });
            router.reload({ only: ["outputtings"] });
        } catch (error) {
            console.error("Error saving edited outputting:", error);
        }
    };

    const handleDelete = async (id) => {
        if (window.confirm("Are you sure you want to delete this entry?")) {
            try {
                await axios.delete(
                    route("outputting.destroy", { outputting: id })
                );
                router.reload({ only: ["outputtings"] });
            } catch (error) {
                console.error("Error deleting outputting:", error);
            }
        }
    };

    return (
        <div className="py-2">
            <div className="mx-auto ">
                <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-2">
                    <div className="p-6 text-gray-900 dark:text-gray-100">
                        <table className="min-w-full">
                            <thead>
                                <tr>
                                    <th className="px-4 py-2">
                                        Google Sheet ID
                                    </th>
                                    <th className="px-4 py-2">Sheet Name</th>
                                    <th className="px-4 py-2">Append</th>
                                    <th className="px-4 py-2">Actions</th>
                                </tr>
                                <tr>
                                    <td className="px-4 py-2">
                                        <TextInput
                                            value={data.sheetId}
                                            onChange={(e) =>
                                                setData(
                                                    "sheetId",
                                                    e.target.value
                                                )
                                            }
                                            className="w-full"
                                        />
                                    </td>
                                    <td className="px-4 py-2">
                                        <TextInput
                                            value={data.sheetName}
                                            onChange={(e) =>
                                                setData(
                                                    "sheetName",
                                                    e.target.value
                                                )
                                            }
                                            className="w-full"
                                        />
                                    </td>
                                    <td className="px-4 py-2 text-center">
                                        <input
                                            type="checkbox"
                                            checked={data.append}
                                            onChange={(e) =>
                                                setData(
                                                    "append",
                                                    e.target.checked
                                                )
                                            }
                                        />
                                    </td>
                                    <td className="px-4 py-2 text-center">
                                        <button
                                            onClick={handleSaveNew}
                                            className="bg-gray-500 py-1 px-3 text-white rounded shadow hover:bg-gray-700"
                                        >
                                            +
                                        </button>
                                    </td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div className="p-6 text-gray-900 dark:text-gray-100">
                        <table className="min-w-full">
                            <thead className="border-b border-gray-300">
                                <tr>
                                    <th className="px-4 py-2 text-left">
                                        Google Sheet ID
                                    </th>
                                    <th className="px-4 py-2 text-left">
                                        Sheet Name
                                    </th>
                                    <th className="px-4 py-2">Append</th>
                                    <th className="px-4 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {outputtings.data.map((outputting) =>
                                    isEditing === outputting.id ? (
                                        <tr key={outputting.id}>
                                            <td className="px-4 py-2">
                                                <TextInput
                                                    value={tempData.sheetId}
                                                    onChange={(e) =>
                                                        setTempData({
                                                            ...tempData,
                                                            sheetId:
                                                                e.target.value,
                                                        })
                                                    }
                                                    className="w-full"
                                                />
                                            </td>
                                            <td className="px-4 py-2">
                                                <TextInput
                                                    value={tempData.sheetName}
                                                    onChange={(e) =>
                                                        setTempData({
                                                            ...tempData,
                                                            sheetName:
                                                                e.target.value,
                                                        })
                                                    }
                                                    className="w-full"
                                                />
                                            </td>
                                            <td className="px-4 py-2 text-center">
                                                <input
                                                    type="checkbox"
                                                    checked={tempData.append}
                                                    onChange={(e) =>
                                                        setTempData({
                                                            ...tempData,
                                                            append: e.target
                                                                .checked,
                                                        })
                                                    }
                                                />
                                            </td>
                                            <td className="px-4 py-2 text-center">
                                                <button
                                                    onClick={() =>
                                                        handleSaveEdit(
                                                            outputting.id
                                                        )
                                                    }
                                                    className="bg-gray-500 py-1 px-3 text-white rounded shadow hover:bg-gray-700 mr-2"
                                                >
                                                    Save
                                                </button>
                                                <button
                                                    onClick={() =>
                                                        setIsEditing(null)
                                                    }
                                                    className="bg-gray-500 py-1 px-3 text-white rounded shadow hover:bg-gray-700"
                                                >
                                                    Cancel
                                                </button>
                                            </td>
                                        </tr>
                                    ) : (
                                        <tr key={outputting.id}>
                                            <td className="px-4 py-2">
                                                {outputting.sheet_id}
                                            </td>
                                            <td className="px-4 py-2">
                                                {outputting.sheet_name}
                                            </td>
                                            <td className="px-4 py-2 text-center">
                                                {outputting.append
                                                    ? "Yes"
                                                    : "No"}
                                            </td>
                                            <td className="px-4 py-2 text-center">
                                                <button
                                                    onClick={() =>
                                                        handleEdit(outputting)
                                                    }
                                                    className="bg-gray-500 py-1 px-3 text-white rounded shadow hover:bg-gray-700 mr-2"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    onClick={() =>
                                                        handleDelete(
                                                            outputting.id
                                                        )
                                                    }
                                                    className="bg-gray-200 py-1 px-3 text-gray-400 rounded shadow hover:bg-red-900 mr-2"
                                                >
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    )
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    );
}
