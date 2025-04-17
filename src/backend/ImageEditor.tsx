import { useEffect, useRef, useState } from 'react';
import { useRecordContext } from 'react-admin';
import { Stage, Layer, Line, Image as KonvaImage } from 'react-konva';
import useImage from 'use-image';
import { Button, TextField } from '@mui/material';
import Konva from 'konva';

interface LineType {
  tool: string;
  points: number[];
}

const ImageEditor = (props: any) => {
  const record = useRecordContext(props);
  const [image] = useImage(record.imageUrl);
  const imageRef = useRef<Konva.Image>(null);
  const [lines, setLines] = useState<LineType[]>([]);
  const [isDrawing, setIsDrawing] = useState(false);
  const [tool, setTool] = useState<string>('pen');
  const [remark, setRemark] = useState<string>('');
  const [imageSize, setImageSize] = useState({ width: 0, height: 0 });

  useEffect(() => {
    if (image) {
      const img = new window.Image();
      img.src = record.imageUrl;
      img.onload = () => {
        setImageSize({ width: img.naturalWidth, height: img.naturalHeight });
      };
    }
  }, [image, record.imageUrl]);

  const handleMouseDown = (e: Konva.KonvaEventObject<MouseEvent>) => {
    setIsDrawing(true);
    const pos = e.target.getStage()?.getPointerPosition();
    if (pos) {
      setLines((prevLines) => [...prevLines, { tool, points: [pos.x, pos.y] }]);
    }
  };

  const handleMouseMove = (e: Konva.KonvaEventObject<MouseEvent>) => {
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
    setIsDrawing(false);
  };

  const handleClear = () => {
    setLines([]);
  };

  const handleSave = async () => {
    const overlayData = {
      postId: record.postId,
      mediaType: 'image',
      mediaUrl: record.imageUrl,
      coordinates: lines,
      description: remark,
      reviewerId: record.reviewerId,
    };

    try {
      const response = await fetch('http://localhost:8098/backend/api/overlay/imagesave', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(overlayData),
      });

      const result = await response.json();
      if (result.success) {
        alert('Overlay data saved successfully!');
      } else {
        alert('Failed to save overlay data.');
      }
    } catch (error) {
      alert('Error saving overlay data.');
    }
  };

  return (
    <div>
      <Stage
        width={imageSize.width}
        height={imageSize.height}
        onMouseDown={handleMouseDown}
        onMouseMove={handleMouseMove}
        onMouseUp={handleMouseUp}
      >
        <Layer>
          <KonvaImage image={image} ref={imageRef} width={imageSize.width} height={imageSize.height} />

          {lines.map((line, i) => (
            <Line
              key={i}
              points={line.points}
              stroke={line.tool === 'eraser' ? 'white' : '#df4b26'}
              strokeWidth={5}
              tension={0.5}
              lineCap="round"
              lineJoin="round"
              globalCompositeOperation={
                line.tool === 'eraser' ? 'destination-out' : 'source-over'
              }
            />
          ))}
        </Layer>
      </Stage>

      <select
        value={tool}
        onChange={(e) => setTool(e.target.value)}
        style={{ display: 'block', marginTop: '10px' }}
      >
        <option value="pen">Pen</option>
      </select>

      <TextField
        label="Comment"
        value={remark}
        onChange={(e) => setRemark(e.target.value)}
        style={{ marginTop: '10px' }}
      />

      <Button
        variant="contained"
        color="secondary"
        onClick={handleClear}
        style={{ marginTop: '10px', marginRight: '10px' }}
      >
        Clear All Drawings
      </Button>

      <Button
        variant="contained"
        color="primary"
        onClick={handleSave}
        style={{ marginTop: '10px' }}
      >
        Save Markup
      </Button>
    </div>
  );
};

export default ImageEditor;

