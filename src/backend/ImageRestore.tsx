import { useEffect, useRef, useState, Fragment } from 'react';
import { useRecordContext } from 'react-admin';
import { Stage, Layer, Line, Image as KonvaImage, Text } from 'react-konva';
import useImage from 'use-image';
import Konva from 'konva';

interface LineType {
  tool: string;
  points: number[];
}

const ImageRestore = (props: any) => {
  const record = useRecordContext(props);
  const [image] = useImage(record.imageUrl);
  const imageRef = useRef<Konva.Image>(null);
  const [lines, setLines] = useState<LineType[]>([]);
  const [description, setDescription] = useState<string>('');
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

  useEffect(() => {
    const fetchOverlayData = async () => {
        try {
            const response = await fetch(
                `http://localhost:8098/backend/api/overlay/fetchimage?postId=${record.id}`
            );
            const data = await response.json();
            if (data.coordinates && Array.isArray(data.coordinates)) {
                const formattedLines = data.coordinates.map((coords: number[], index: number) => ({
                    points: coords,
                }));
                setLines(formattedLines);
                setDescription(data.description);
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

return (
    <div>
      <Stage width={imageSize.width} height={imageSize.height}>
        <Layer>
          <KonvaImage
            image={image}
            ref={imageRef}
            width={imageSize.width}
            height={imageSize.height}
          />

          {Array.isArray(lines) && lines.length > 0 ? (
            lines.map((line, index) => (
              <Fragment key={index}>
                <Line
                  points={line.points}
                  stroke={line.tool === 'pen' ? '#df4b26' : '#000'}
                  strokeWidth={5}
                  tension={0.5}
                  lineCap="round"
                  lineJoin="round"
                />

                {description && description.length > 0 && (
                  <Text
                    x={imageSize.width / 2 - description.length * 3}
                    y={imageSize.height - 30}
                    text={description}
                    fontSize={20}
                    fill="black"
                    fontStyle="italic"
                  />
                )}
              </Fragment>
            ))
          ) : (
            <Text
              x={imageSize.width / 2 - 100}
              y={imageSize.height - 30}
              text="No overlay data available."
              fontSize={20}
              fill="red"
              align="center"
            />
          )}
        </Layer>
      </Stage>
    </div>
  );
};

export default ImageRestore;
