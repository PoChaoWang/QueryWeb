import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
import axios from "axios";

export default function Index({ auth, connections }) {
    const sortedClients = connections.sort();

    const handleSelectGoogleConnection = async (client) => {
        try {
            await axios.post("/set-connection", { client });
            window.location.href = `connections/` + `${client}` + `/google`;
        } catch (error) {
            console.error("Failed to set connection:", error);
            alert("Failed to set connection. Please try again.");
        }
    };

    const handleSelectMetaConnection = async (client) => {
        try {
            await axios.post("/set-connection", { client });
            window.location.href = `connections/` + `${client}` + `/meta`;
        } catch (error) {
            console.error("Failed to set connection:", error);
            alert("Failed to set connection. Please try again.");
        }
    };

    // const handleSelectSftpConnection = async (client) => {
    //     try {
    //         await axios.post("/set-connection", { client });
    //         window.location.href = `connections/` + `${client}` + `/sftp`;
    //     } catch (error) {
    //         console.error("Failed to set connection:", error);
    //         alert("Failed to set connection. Please try again.");
    //     }
    // };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Connections
                    </h2>
                </div>
            }
        >
            <Head title="Connections" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <ul>
                                {sortedClients.map((client) => (
                                    <li
                                        key={client}
                                        className="mb-2 border-b-2 border-gray-500"
                                    >
                                        <div className="mb-2 flex justify-between">
                                            {client}
                                            <div>
                                                <button
                                                    onClick={() =>
                                                        handleSelectGoogleConnection(
                                                            client
                                                        )
                                                    }
                                                    className="bg-gray-500 py-1 px-3 text-white rounded shadow transition-all hover:bg-gray-600 mx-2"
                                                >
                                                    Google
                                                </button>

                                                <button
                                                    onClick={() =>
                                                        handleSelectMetaConnection(
                                                            client
                                                        )
                                                    }
                                                    className="bg-gray-500 py-1 px-3 text-white rounded shadow transition-all hover:bg-gray-600 mx-2"
                                                >
                                                    Meta
                                                </button>

                                                {/* <button
                                                    onClick={() =>
                                                        handleSelectSftpConnection(
                                                            client
                                                        )
                                                    }
                                                    className="bg-gray-500 py-1 px-3 text-white rounded shadow transition-all hover:bg-gray-600 mx-2"
                                                >
                                                    SFTP
                                                </button> */}
                                            </div>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                    {/* <pre className="text-white">
                        {JSON.stringify(tableDetails, null, 2)}
                    </pre> */}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
