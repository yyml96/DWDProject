import React, { useEffect, useRef, useState } from "react";
import { useRecordContext } from "react-admin";
import { Stage, Layer, Line } from "react-konva";

interface LineType {
  tool: string;
  points: number[];
  timestamp: number;
}

const VideoOverlayView = (props: any) => {
  const record = useRecordContext(props);
  const videoRef = useRef<HTMLVideoElement>(null);
  const [lines, setLines] = useState<LineType[]>([]);
  const [currentLines, setCurrentLines] = useState<LineType[]>([]);
  const [description, setDescription] = useState<string>("");
  const [videoSize, setVideoSize] = useState({ width: 0, height: 0 });

  useEffect(() => {
    const fetchOverlayData = async () => {
      try {
        const response = await fetch(
          `http://localhost:8098/backend/api/overlay/fetchvideo?postId=${record.id}`
        );
        const data = await response.json();
        if (data.coordinates && Array.isArray(data.coordinates)) {
          const formattedLines = data.coordinates.map((coord: any) => ({
            tool: coord.tool,
            points: coord.points,
            timestamp: coord.timestamp,
          }));
          setLines(formattedLines);
          setDescription(data.description || "");
        } else {
          console.error("No valid coordinates found:", data);
          setLines([]);
        }
      } catch (error) {
        console.error("Error fetching overlay data:", error);
        setLines([]);
      }
    };

    fetchOverlayData();
  }, [record.id]);

  const handleTimeUpdate = () => {
    const video = videoRef.current;
    if (!video || !lines.length) return;

    const currentTime = video.currentTime;

    // Filter lines that should appear at the current timestamp
    const visibleLines = lines.filter((line) => line.timestamp <= currentTime);
    setCurrentLines(visibleLines);
  };

  if (!record.videoUrl) {
    return <p>No video associated with this todo.</p>;
  }

  return (
    <div style={{ textAlign: "center" }}>
      {/* Video Player */}
      <div
        style={{
          position: "relative",
          width: `${videoSize.width}px`,
          height: `${videoSize.height}px`,
          margin: "0 auto",
        }}
      >
        <video
          ref={videoRef}
          src={record.videoUrl}
          controls
          style={{
            display: "block",
            width: "100%",
            height: "100%",
            zIndex: 1,
          }}
          onTimeUpdate={handleTimeUpdate} // Update visible lines as video plays
          onLoadedMetadata={() => {
            const video = videoRef.current;
            if (video) {
              setVideoSize({
                width: video.videoWidth,
                height: video.videoHeight,
              });
            }
          }}
        />

        {/* Overlay Stage */}
        <Stage
          width={videoSize.width}
          height={videoSize.height}
          style={{
            position: "absolute",
            top: 0,
            left: 0,
            zIndex: 2,
            pointerEvents: "none",
          }}
        >
          <Layer>
            {currentLines.map((line, index) => (
              <Line
                key={index}
                points={line.points}
                stroke={line.tool === "eraser" ? "white" : "#df4b26"}
                strokeWidth={5}
                tension={0.5}
                lineCap="round"
                lineJoin="round"
                globalCompositeOperation={
                  line.tool === "eraser" ? "destination-out" : "source-over"
                }
              />
            ))}
          </Layer>
        </Stage>
      </div>

      {/* Description */}
      <p style={{ marginTop: "20px" }}>{description}</p>
    </div>
  );
};

export default VideoOverlayView;