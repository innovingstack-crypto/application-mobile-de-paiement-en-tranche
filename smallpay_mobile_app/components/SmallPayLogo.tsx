import React from 'react';
import Svg, { G, Polygon, Text as SvgText } from 'react-native-svg';

type SmallPayLogoProps = {
  size?: number;
  width?: number;
  height?: number;
  smallColor?: string;
  payColor?: string;
};

export function SmallPayLogo({
  size = 180,
  width,
  height,
  smallColor = '#3392eb',
  payColor = '#fbbf24',
}: SmallPayLogoProps) {
  const finalWidth = width ?? size;
  const finalHeight = height ?? size;

  return (
    <Svg width={finalWidth} height={finalHeight} viewBox="0 0 500 500" preserveAspectRatio="xMidYMid meet">
      <Polygon
        points="350,50 250,200 300,200 150,450 250,300 200,300"
        fill="#ffffff"
        stroke="#a7dcfb"
        strokeWidth={5}
        strokeLinejoin="round"
      />

      <G>
        <SvgText
          x={250}
          y={230}
          textAnchor="middle"
          fontSize={120}
          fontWeight="900"
          fontFamily="System"
          fill={smallColor}
        >
          SMALL
        </SvgText>

        <SvgText
          x={250}
          y={330}
          textAnchor="middle"
          fontSize={120}
          fontWeight="900"
          fontFamily="System"
          fill={payColor}
        >
          PAY
        </SvgText>
      </G>
    </Svg>
  );
}
