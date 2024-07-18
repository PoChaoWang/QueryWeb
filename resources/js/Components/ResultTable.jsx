import React from "react";

const ResultsTable = ({ result, maxRows }) => {
    if (!result || result.length === 0) {
        return <div>No data available</div>;
    }

    const headers = Object.keys(result[0]);
    const limitedResult = maxRows ? result.slice(0, maxRows) : result;

    return (
        <div className="overflow-auto">
            <table className="min-w-full bg-gray-800 text-white">
                <thead>
                    <tr>
                        {headers.map((header) => (
                            <th
                                key={header}
                                className="px-4 py-2 border-b-2 border-gray-700"
                            >
                                {header}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {limitedResult.map((row, rowIndex) => (
                        <tr key={rowIndex} className="border-b border-gray-700">
                            {headers.map((header) => (
                                <td key={header} className="px-4 py-2">
                                    {row[header]}
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default ResultsTable;
