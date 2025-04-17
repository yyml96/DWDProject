import { useEffect, useRef, useState } from "react";
import { useRecordContext } from "react-admin";
import { Stage, Layer, Line } from "react-konva";
import { Button, TextField } from "@mui/material";
import { Timestamp } from "mongodb";
import { timeStamp } from "console";

interface LineType {
  tool: string;
  points: number[];
  timestamp: number; // Add timestamp property
}

const VideoEditor = (props: any) => {
  const record = useRecordContext(props);
  const videoRef = useRef<HTMLVideoElement>(null);
  const [lines, setLines] = useState<LineType[]>([]);
  const [isDrawing, setIsDrawing] = useState(false);
  const [tool, setTool] = useState<string>("pen");
  const [remark, setRemark] = useState<string>("");
  const [videoSize, setVideoSize] = useState({ width: 0, height: 0 });

  useEffect(() => {
    const video = videoRef.current;
    if (video) {
      video.onloadedmetadata = () =>
        setVideoSize({ width: video.videoWidth, height: video.videoHeight });
    }
  }, [record?.videoUrl]);

  const handleMouseDown = (e: any) => {
    const video = videoRef.current;
  if (video) {
    video.pause(); // Pause the video
  }

    setIsDrawing(true);
    const pos = e.target.getStage()?.getPointerPosition();
    if (pos) {
      setLines((prevLines) => [
        ...prevLines,
        {
          tool,
          points: [pos.x, pos.y],
          timestamp: 0, // Record current playback time
        },
      ]);
    }
  };

  const handleMouseMove = (e: any) => {
    if (!isDrawing) return;
    const stage = e.target.getStage();
    const point = stage?.getPointerPosition();
    if (!point) return;

    setLines((prevLines) => {
      const lastLine = { ...prevLines[prevLines.length - 1] };
      lastLine.points = lastLine.points.concat([point.x, point.y]);
      const newLines = prevLines.slice(0, prevLines.length - 1);
      return [...newLines, lastLine];
    });
  };

  const handleMouseUp = () => {
    const video = videoRef.current;
  if (video) {
    const currentTime = video.currentTime; // Get current video time
    setLines((prevLines) => {
      const lastLine = { ...prevLines[prevLines.length - 1] };
      lastLine.timestamp = currentTime; // Assign the timestamp
      return [...prevLines.slice(0, -1), lastLine];
      });
    }

    setIsDrawing(false);
  };

  const handleClear = () => {
    setLines([]);
  };

  const handleSave = async () => {
    const overlayData = {
      postId: record.postId,
      mediaType: "video",
      mediaUrl: record.videoUrl,
      coordinates: lines.map((line) => ({
        tool: line.tool,
        points: line.points,
        timestamp: line.timestamp, // Save the timestamp
      })),
      description: remark,
      reviewerId: record.reviewerId,
    };

    try {
      const response = await fetch("http://localhost:8098/backend/api/overlay/videosave", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(overlayData),
      });

      const result = await response.json();
      if (result.success) {
        alert("Overlay data saved successfully!");
      } else {
        alert("Failed to save overlay data.");
      }
    } catch (error) {
      alert("Error saving overlay data.");
    }
  };

  if (!record?.videoUrl) {
    return <p>No video associated with this todo.</p>;
  }

  return (
    <div style={{ textAlign: "center" }}>
    {/* Video and Overlay Container */}
    <div
      style={{
        position: "relative",
        width: `${videoSize.width}px`,
        height: `${videoSize.height}px`,
        margin: "0 auto",
      }}
    >
      {/* Video */}
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
      />

      {/* Konva Stage Overlay */}
      <Stage
        width={videoSize.width}
        height={videoSize.height * 0.939}
        style={{
          position: "absolute",
          top: 0,
          left: 0,
          zIndex: 2,
          border: "2px solid red",
          //pointerEvents: "none"
        }}
        onMouseDown={handleMouseDown}
        onMouseMove={handleMouseMove}
        onMouseUp={handleMouseUp}
      >
        <Layer>
          {lines.map((line, i) => (
            <Line
              key={i}
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

    {/* Controls Below the Video */}
    <div style={{ marginTop: "20px", textAlign: "center" }}>
      {/* Tool Selector */}
      <select
        value={tool}
        onChange={(e) => setTool(e.target.value)}
        style={{ display: "block", margin: "10px auto" }}
      >
        <option value="pen">Pen</option>
      </select>

      {/* Comment Input */}
      <TextField
        label="Comment"
        value={remark}
        onChange={(e) => setRemark(e.target.value)}
        style={{ margin: "10px auto", display: "block", width: "50%" }}
      />

      {/* Buttons */}
      <div style={{ marginTop: "10px" }}>
        <Button
          variant="contained"
          color="secondary"
          onClick={handleClear}
          style={{ marginRight: "10px" }}
        >
          Clear Drawings
        </Button>
        <Button variant="contained" color="primary" onClick={handleSave}>
          Save Markup
        </Button>
      </div>
    </div>
  </div>
);
};

export default VideoEditor;