import React, { useState } from "react";
import axios from "axios";
import { useForm } from "@inertiajs/react";
import SelectInput from "@/Components/SelectInput";

export default function ScheduleTable({ schedules }) {
    const [isEditing, setIsEditing] = useState(null);
    const [tempData, setTempData] = useState({
        week_day: "",
        hour: "",
        minute: "",
        append: false,
    });

    const { data, setData, post, reset } = useForm({
        week_day: "",
        hour: "",
        minute: "",
        append: false,
    });

    const handleSaveNew = async () => {
        try {
            await post(route("schedule.store"), {
                onSuccess: () => reset(),
            });
        } catch (error) {
            console.error("Error saving new schedule:", error);
        }
    };

    const handleEdit = (schedule) => {
        const [hour, minute] = schedule.time.split(":");
        setIsEditing(schedule.id);
        setTempData({
            week_day: schedule.week_day,
            hour,
            minute,
            append: schedule.append,
        });
    };

    const handleSaveEdit = async (id) => {
        const time = `${tempData.hour}:${tempData.minute}`;
        try {
            await axios.put(route("schedule.update", id), {
                ...tempData,
                time,
            });
            setIsEditing(null);
            setTempData({
                week_day: "",
                hour: "",
                minute: "",
                append: false,
            });
        } catch (error) {
            console.error("Error saving edited schedule:", error);
        }
    };

    const handleDelete = async (id) => {
        if (window.confirm("Are you sure you want to delete this entry?")) {
            try {
                await axios.delete(route("schedule.destroy", id));
            } catch (error) {
                console.error("Error deleting schedule:", error);
            }
        }
    };

    const hours = Array.from({ length: 24 }, (_, i) =>
        i.toString().padStart(2, "0")
    );
    const minutes = ["00", "15", "30", "45"];

    return (
        <div className="py-2">
            <div className="mx-auto ">
                <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-2">
                    <div className="p-6 text-gray-900 dark:text-gray-100">
                        <table className="min-w-full">
                            <thead>
                                <tr>
                                    <th className="px-4 py-2">Week Day</th>
                                    <th className="px-4 py-2">Hour</th>
                                    <th className="px-4 py-2">Minute</th>
                                    <th className="px-4 py-2">Actions</th>
                                </tr>
                                <tr>
                                    <td className="px-4 py-2">
                                        <SelectInput
                                            value={data.week_day}
                                            onChange={(e) =>
                                                setData(
                                                    "week_day",
                                                    e.target.value
                                                )
                                            }
                                            className="w-full"
                                        >
                                            <option value="">
                                                Select Week Day
                                            </option>
                                            <option value="Everyday">
                                                Everyday
                                            </option>
                                            <option value="Monday">
                                                Monday
                                            </option>
                                            <option value="Tuesday">
                                                Tuesday
                                            </option>
                                            <option value="Wednesday">
                                                Wednesday
                                            </option>
                                            <option value="Thursday">
                                                Thursday
                                            </option>
                                            <option value="Friday">
                                                Friday
                                            </option>
                                            <option value="Saturday">
                                                Saturday
                                            </option>
                                            <option value="Sunday">
                                                Sunday
                                            </option>
                                        </SelectInput>
                                    </td>
                                    <td className="px-4 py-2">
                                        <SelectInput
                                            value={data.hour}
                                            onChange={(e) =>
                                                setData("hour", e.target.value)
                                            }
                                            className="w-full"
                                        >
                                            <option value="">Hour</option>
                                            {hours.map((hour) => (
                                                <option key={hour} value={hour}>
                                                    {hour}
                                                </option>
                                            ))}
                                        </SelectInput>
                                    </td>
                                    <td className="px-4 py-2">
                                        <SelectInput
                                            value={data.minute}
                                            onChange={(e) =>
                                                setData(
                                                    "minute",
                                                    e.target.value
                                                )
                                            }
                                            className="w-full"
                                        >
                                            <option value="">Minute</option>
                                            {minutes.map((minute) => (
                                                <option
                                                    key={minute}
                                                    value={minute}
                                                >
                                                    {minute}
                                                </option>
                                            ))}
                                        </SelectInput>
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
                                        Week Day
                                    </th>
                                    <th className="px-4 py-2 text-left">
                                        Hour
                                    </th>
                                    <th className="px-4 py-2 text-left">
                                        Minute
                                    </th>
                                    <th className="px-4 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {schedules.data.map((schedule) =>
                                    isEditing === schedule.id ? (
                                        <tr key={schedule.id}>
                                            <td className="px-4 py-2">
                                                <SelectInput
                                                    defaultValue={
                                                        tempData.week_day
                                                    }
                                                    onChange={(e) =>
                                                        setTempData({
                                                            ...tempData,
                                                            week_day:
                                                                e.target.value,
                                                        })
                                                    }
                                                    className="w-full"
                                                >
                                                    <option value="">
                                                        Select Status
                                                    </option>
                                                    <option value="Everyday">
                                                        Everyday
                                                    </option>
                                                    <option value="Monday">
                                                        Monday
                                                    </option>
                                                    <option value="Tuesday">
                                                        Tuesday
                                                    </option>
                                                    <option value="Wednesday">
                                                        Wednesday
                                                    </option>
                                                    <option value="Thursday">
                                                        Thursday
                                                    </option>
                                                    <option value="Friday">
                                                        Friday
                                                    </option>
                                                    <option value="Saturday">
                                                        Saturday
                                                    </option>
                                                    <option value="Sunday">
                                                        Sunday
                                                    </option>
                                                </SelectInput>
                                            </td>
                                            <td className="px-4 py-2">
                                                <SelectInput
                                                    defaultValue={
                                                        schedule.time.split(
                                                            ":"
                                                        )[0]
                                                    }
                                                    onChange={(e) =>
                                                        setTempData({
                                                            ...tempData,
                                                            hour: e.target
                                                                .value,
                                                        })
                                                    }
                                                    className="w-full"
                                                >
                                                    <option value="">
                                                        Hour
                                                    </option>
                                                    {hours.map((hour) => (
                                                        <option
                                                            key={hour}
                                                            value={hour}
                                                        >
                                                            {hour}
                                                        </option>
                                                    ))}
                                                </SelectInput>
                                            </td>
                                            <td className="px-4 py-2">
                                                <SelectInput
                                                    defaultValue={
                                                        schedule.time.split(
                                                            ":"
                                                        )[1]
                                                    }
                                                    onChange={(e) =>
                                                        setTempData({
                                                            ...tempData,
                                                            minute: e.target
                                                                .value,
                                                        })
                                                    }
                                                    className="w-full"
                                                >
                                                    <option value="">
                                                        Minute
                                                    </option>
                                                    {minutes.map((minute) => (
                                                        <option
                                                            key={minute}
                                                            value={minute}
                                                        >
                                                            {minute}
                                                        </option>
                                                    ))}
                                                </SelectInput>
                                            </td>
                                            <td className="px-4 py-2 text-center">
                                                <button
                                                    onClick={() =>
                                                        handleSaveEdit(
                                                            schedule.id
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
                                        <tr key={schedule.id}>
                                            <td className="px-4 py-2">
                                                {schedule.week_day}
                                            </td>
                                            <td className="px-4 py-2">
                                                {schedule.time.split(":")[0]}
                                            </td>
                                            <td className="px-4 py-2">
                                                {schedule.time.split(":")[1]}
                                            </td>

                                            <td className="px-4 py-2 text-center">
                                                <button
                                                    onClick={() =>
                                                        handleEdit(schedule)
                                                    }
                                                    className="bg-gray-500 py-1 px-3 text-white rounded shadow hover:bg-gray-700 mr-2"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    onClick={() =>
                                                        handleDelete(
                                                            schedule.id
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
                <pre className="text-white">
                    {/* {JSON.stringify(schedules, null, 2)} */}
                </pre>
            </div>
        </div>
    );
}
